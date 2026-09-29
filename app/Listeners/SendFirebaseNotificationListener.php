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
            $shopId = null;

            // 1. If Booking action, queue SMS template for SMS Gateway
            if ($event->modelClass === Booking::class || is_a($event->modelClass, Booking::class, true)) {
                $booking = Booking::with(['customer', 'service', 'barber', 'barberShop'])->find($event->modelId);
                if ($booking) {
                    $shopId = $booking->barber_shop_id;

                    if ($booking->customer && $booking->customer->phone) {
                        $templateType = match ($event->action) {
                            'created' => 'confirmation',
                            'updated' => 'reminder',
                            default => 'confirmation',
                        };

                        $parsedMessage = MessageTemplate::parseForBooking($booking, $templateType);

                        MessageQueue::create([
                            'barber_shop_id' => $booking->barber_shop_id,
                            'booking_id' => $booking->id,
                            'channel' => 'sms',
                            'phone_number' => $booking->customer->phone,
                            'message_content' => $parsedMessage,
                            'scheduled_at' => now(),
                            'status' => 'pending',
                            'retry_count' => 0,
                        ]);

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
            }

            // 2. Dispatch FCM Push Notification STRICTLY ISOLATED PER SHOP ID!
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

            if (!$shopId && auth()->check()) {
                $shopId = auth()->user()->barber_shop_id;
            }

            $customData = [
                'shop_id' => (string) ($shopId ?? 0),
                'action' => (string) $event->action,
                'model' => (string) class_basename($event->modelClass),
                'model_id' => (string) $event->modelId,
            ];

            if ($shopId) {
                $sentCount = $this->firebaseService->sendToShop($shopId, $title, $body, $customData);
                Log::info("FCM Push Notification sent for shop #{$shopId} event [{$event->event}] to {$sentCount} devices.");
            } else {
                $sentCount = $this->firebaseService->sendToAllDevices($title, $body);
                Log::info("FCM Push Notification sent globally for event [{$event->event}] to {$sentCount} devices.");
            }
        } catch (\Throwable $e) {
            Log::error("FCM Push Notification Listener Error for event [{$event->event}]: " . $e->getMessage());
        }
    }
}
