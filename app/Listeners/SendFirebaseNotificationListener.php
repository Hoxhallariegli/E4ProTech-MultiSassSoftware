<?php

namespace App\Listeners;

use App\Events\FirebaseNotificationRequested;
use App\Services\FirebaseService;
use App\Models\Booking;
use App\Models\MessageTemplate;
use App\Models\MessageQueue;
use App\Models\MessageLog;
use App\Models\DeviceToken;
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

            // 1. If Booking action, custom logic for Push and SMS Gateway
            if ($event->modelClass === Booking::class || is_a($event->modelClass, Booking::class, true)) {
                $booking = Booking::with(['customer', 'service', 'barber', 'barberShop'])->find($event->modelId);
                if ($booking) {
                    $shopId = $booking->barber_shop_id;
                    $customerName = $booking->customer?->name ?? 'Klient';
                    $serviceName = $booking->service?->name ?? 'Shërbim';
                    $barberName = $booking->barber?->name ?? 'Staf';
                    $time = $booking->appointment_at ? $booking->appointment_at->format('H:i d/m/Y') : '';

                    // Custom Push Notification Body
                    if ($event->action === 'created') {
                        $body = "Klienti {$customerName} rezervoi {$serviceName} për orën {$time} tek {$barberName}.";
                    } elseif ($event->action === 'updated') {
                        $body = "Rezervimi i {$customerName} u përditësua për {$time}.";
                    }

                    if ($booking->customer && $booking->customer->phone) {
                        $templateType = match ($event->action) {
                            'created' => 'confirmation',
                            'updated' => 'reminder',
                            default => 'confirmation',
                        };

                        $parsedMessage = MessageTemplate::parseForBooking($booking, $templateType);

                        $queue = MessageQueue::create([
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
                            'message' => $parsedMessage,
                            'status' => 'pending', // NOT sent yet, waiting for app to process
                            'sent_at' => null,
                        ]);

                        Log::info("Queued custom SMS template for shop #{$booking->barber_shop_id} to {$booking->customer->phone}");

                        // Push SEND_SMS trigger to the Gateway device specifically
                        $gatewayDevice = DeviceToken::where('barber_shop_id', $shopId)->where('is_sms_gateway', true)->first();
                        if ($gatewayDevice && $gatewayDevice->fcm_token) {
                            $this->firebaseService->sendNotification(
                                'SMS Gateway',
                                'Duke dërguar SMS...',
                                $gatewayDevice->fcm_token,
                                [
                                    'action' => 'SEND_SMS',
                                    'sms_id' => (string) $queue->id,
                                    'phone' => (string) $booking->customer->phone,
                                    'body' => (string) $parsedMessage,
                                ]
                            );
                            Log::info("Triggered SEND_SMS push directly to gateway device for shop #{$shopId}");
                        }
                    }
                }
            }

            // 2. Dispatch FCM Push Notification STRICTLY ISOLATED PER SHOP ID!
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
