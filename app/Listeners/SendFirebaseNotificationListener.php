<?php

namespace App\Listeners;

use App\Events\FirebaseNotificationRequested;
use App\Services\FirebaseService;
use App\Models\Booking;
use App\Models\MessageTemplate;
use App\Models\MessageQueue;
use App\Models\MessageLog;
use Illuminate\Support\Facades\Log;

class SendFirebaseNotificationListener
{
    public function __construct(protected FirebaseService $firebaseService)
    {
    }

    public function handle(FirebaseNotificationRequested $event): void
    {
        try {
            // 1. If the event is a Booking action, process SMS Queue using shop's custom MessageTemplate
            if ($event->modelClass === Booking::class || is_a($event->modelClass, Booking::class, true)) {
                $booking = Booking::with(['customer', 'service', 'barber', 'barberShop'])->find($event->modelId);
                if ($booking && $booking->customer && $booking->customer->phone) {
                    $templateType = match ($event->action) {
                        'created' => 'confirmation',
                        'updated' => 'reminder',
                        default => 'confirmation',
                    };

                    $parsedMessage = MessageTemplate::parseForBooking($booking, $templateType);

                    // Add to MessageQueue for SMS Gateway
                    $queueItem = MessageQueue::create([
                        'barber_shop_id' => $booking->barber_shop_id,
                        'booking_id' => $booking->id,
                        'channel' => 'sms',
                        'phone_number' => $booking->customer->phone,
                        'message_content' => $parsedMessage,
                        'scheduled_at' => now(),
                        'status' => 'pending',
                        'retry_count' => 0,
                    ]);

                    // Log in MessageLog
                    MessageLog::create([
                        'barber_shop_id' => $booking->barber_shop_id,
                        'customer_id' => $booking->customer_id,
                        'channel' => 'sms',
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);

                    Log::info("Queued custom SMS template for shop #{$booking->barber_shop_id} to {$booking->customer->phone}");
                }
            }

            // 2. Dispatch FCM Push Notification to Mobile App
            $actionLabel = match ($event->action) {
                'created' => 'krijua',
                'updated' => 'përditësua',
                'deleted' => 'fshi',
                default => 'ndryshua',
            };

            $moduleName = match (class_basename($event->modelClass)) {
                'Booking' => 'Rezervim',
                'Customer' => 'Klient',
                'Payment' => 'Pagesë',
                'Barber' => 'Punonjës',
                'Service' => 'Shërbim',
                'BarberShop' => 'Sallon',
                default => class_basename($event->modelClass),
            };

            $title = "Njoftim: {$moduleName} u {$actionLabel}! 🔔";
            $body = "Regjistrimi #{$event->modelId} te moduli {$moduleName} u {$actionLabel} me sukses.";

            $sentCount = $this->firebaseService->sendToAllDevices($title, $body);

            Log::info("FCM Push Notification sent for event [{$event->event}] to {$sentCount} devices.");
        } catch (\Throwable $e) {
            Log::error("FCM Push Notification Listener Error for event [{$event->event}]: " . $e->getMessage());
        }
    }
}
