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
    protected $signature = 'sms:send-reminders';
    protected $description = 'Generate SMS & WhatsApp reminders for bookings X hours ahead based on shop settings.';

    public function handle()
    {
        $tz = config('app.timezone', 'Europe/Tirane');
        $now = Carbon::now($tz);
        $this->info("Checking for upcoming bookings at {$now->format('Y-m-d H:i:s')} ({$tz})...");

        // Purge any duplicate pending queue rows for the same booking_id to prevent duplicate dispatches
        $allPending = MessageQueue::whereIn('status', ['pending', 'queued', 'processing'])->get();
        $seenBookings = [];
        foreach ($allPending as $pItem) {
            $key = "{$pItem->booking_id}:{$pItem->template_type}";
            if (isset($seenBookings[$key])) {
                $pItem->delete();
                Log::info("🧹 Purged duplicate queue row ID #{$pItem->id} for key {$key}");
            } else {
                $seenBookings[$key] = true;
            }
        }

        // We fetch active bookings
        $bookings = Booking::with(['barberShop', 'customer', 'service', 'barber'])
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereNotNull('appointment_at')
            ->whereRaw("appointment_at > ?", [$now->format('Y-m-d H:i:s')])
            ->get();

        $count = 0;

        foreach ($bookings as $booking) {
            $shop = $booking->barberShop;
            $barber = $booking->barber;

            if (!$shop || !$booking->customer || !$booking->customer->phone) {
                continue;
            }

            // Check if shop or barber has SMS / WhatsApp enabled
            $shopSmsEnabled = (bool) ($shop->sms_enabled ?? true);
            $shopWaEnabled = (bool) ($shop->whatsapp_enabled ?? true);
            $barberWaEnabled = (bool) ($barber->whatsapp_enabled ?? true);

            if (!$shopSmsEnabled && !$shopWaEnabled && !$barberWaEnabled) {
                continue;
            }

            // STRICT DEDUPLICATION: If ANY queue or log record already exists for this booking_id, DO NOT CREATE ANOTHER!
            $alreadyExists = MessageQueue::where('booking_id', $booking->id)
                ->where(function($q) {
                    $q->where('template_type', 'reminder')
                      ->orWhere('channel', 'whatsapp')
                      ->orWhere('channel', 'sms');
                })
                ->exists()
                || MessageLog::where('barber_shop_id', $shop->id)
                ->where('customer_id', $booking->customer_id)
                ->where('created_at', '>=', $booking->created_at)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $rawVal = $shop?->reminder_hours_before;
            $hours = (float) ($rawVal !== null && is_numeric($rawVal) && (float)$rawVal > 0 ? $rawVal : 2);
            $reminderMins = (int) round($hours * 60);
            $reminderMins = max(10, $reminderMins);

            $appointmentTime = Carbon::parse($booking->appointment_at, $tz);
            $createdAtTime = Carbon::parse($booking->created_at, $tz);

            // Do not send separate reminder if appointment was booked last-minute
            if ($appointmentTime->diffInMinutes($now) < 10 || $appointmentTime->diffInMinutes($createdAtTime) < 30) {
                continue;
            }

            $reminderTime = $appointmentTime->copy()->subMinutes($reminderMins);

            // If the time to send the reminder is now or has passed
            if ($now->greaterThanOrEqualTo($reminderTime)) {
                $preferredChannel = ($barberWaEnabled || $shopWaEnabled) ? 'whatsapp' : 'sms';
                $parsedMessage = MessageTemplate::parseForBooking($booking, 'reminder', null, $preferredChannel);

                MessageQueue::create([
                    'barber_shop_id' => $shop->id,
                    'booking_id' => $booking->id,
                    'channel' => $preferredChannel,
                    'template_type' => 'reminder',
                    'phone_number' => $booking->customer->phone,
                    'message_content' => $parsedMessage,
                    'scheduled_at' => now(),
                    'status' => 'pending',
                    'retry_count' => 0,
                ]);

                $count++;
                $this->line("Queued reminder for Booking #{$booking->id} (Shop #{$shop->id}, Channel: {$preferredChannel})");
            }
        }

        $this->info("Queued {$count} reminders.");
    }
}
