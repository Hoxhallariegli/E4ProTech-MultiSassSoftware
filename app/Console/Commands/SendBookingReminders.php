<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\MessageQueue;
use App\Models\MessageTemplate;
use App\Models\MessageLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendBookingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate SMS reminders for bookings X hours ahead based on shop settings.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $this->info("Checking for upcoming bookings at {$now}...");

        // We fetch active bookings that haven't had a reminder queued yet
        $bookings = Booking::with(['barberShop', 'customer', 'service', 'barber'])
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereNotNull('appointment_at')
            ->where('appointment_at', '>', $now)
            ->get();

        $count = 0;

        foreach ($bookings as $booking) {
            $shop = $booking->barberShop;

            if (!$shop || !$shop->sms_enabled || !$booking->customer || !$booking->customer->phone) {
                continue;
            }

            $reminderMins = (int) ($shop->reminder_hours_before ?: 30);
            if ($reminderMins <= 6) {
                $reminderMins = $reminderMins * 60; // Convert 1h, 2h, 4h to minutes
            }

            $appointmentTime = Carbon::parse($booking->appointment_at);

            // Do not send reminder if appointment is less than 15 minutes away (too close to creation)
            if ($appointmentTime->diffInMinutes($now) < 15) {
                continue;
            }

            $reminderTime = $appointmentTime->copy()->subMinutes($reminderMins);

            // If the time to send the reminder is now or has passed
            if ($now->greaterThanOrEqualTo($reminderTime)) {

                $alreadyQueued = MessageQueue::where('booking_id', $booking->id)
                    ->where('channel', 'sms')
                    ->where('message_content', 'like', '%Rikujtesë%')
                    ->exists();

                if ($alreadyQueued) {
                    continue;
                }

                $parsedMessage = MessageTemplate::parseForBooking($booking, 'reminder');

                // Add explicit indicator it's a reminder so we don't re-queue it again
                if(!str_contains($parsedMessage, 'Rikujtesë')) {
                   $parsedMessage = "Rikujtesë: " . $parsedMessage;
                }

                MessageQueue::create([
                    'barber_shop_id' => $shop->id,
                    'booking_id' => $booking->id,
                    'channel' => 'sms',
                    'phone_number' => $booking->customer->phone,
                    'message_content' => $parsedMessage,
                    'scheduled_at' => now(), // Queue it to go out immediately
                    'status' => 'pending',
                    'retry_count' => 0,
                ]);

                MessageLog::create([
                    'barber_shop_id' => $shop->id,
                    'customer_id' => $booking->customer_id,
                    'channel' => 'sms',
                    'message' => $parsedMessage,
                    'status' => 'pending',
                    'sent_at' => null,
                ]);

                $count++;
                $this->line("Queued reminder for Booking #{$booking->id} (Shop #{$shop->id})");
            }
        }

        $this->info("Queued {$count} SMS reminders.");
    }
}
