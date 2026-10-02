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
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendFirebaseNotificationListener
{
    public $tries = 3;

    public function __construct(protected FirebaseService $firebaseService)
    {
    }

    public function handle(FirebaseNotificationRequested $event): void
    {
        try {
            Log::info("📌 [STEP 4] SendFirebaseNotificationListener received event [{$event->event}] Action: [{$event->action}] Model ID: [{$event->modelId}]");
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

                    // Queue SMS confirmation AND scheduled reminder when booking is newly created
                    if ($event->action === 'created' && $booking->customer && $booking->customer->phone) {
                        $parsedConfirmation = MessageTemplate::parseForBooking($booking, 'confirmation');
                        $formattedPhone = $this->formatPhone($booking->customer->phone);

                        // 1. Direct Confirmation SMS (Immediate) - Check if already created
                        $existingConfirmation = MessageQueue::where('booking_id', $booking->id)
                            ->where('channel', 'sms')
                            ->where('message_content', $parsedConfirmation)
                            ->exists();

                        if (!$existingConfirmation) {
                            $queue = MessageQueue::create([
                                'barber_shop_id' => $booking->barber_shop_id,
                                'booking_id' => $booking->id,
                                'channel' => 'sms',
                                'phone_number' => $formattedPhone,
                                'message_content' => $parsedConfirmation,
                                'scheduled_at' => now(),
                                'status' => 'pending',
                                'retry_count' => 0,
                            ]);
                            \App\Models\AuditTrail::log($queue, 'create', 'MessageQueues');

                            $msgLog = MessageLog::create([
                                'barber_shop_id' => $booking->barber_shop_id,
                                'customer_id' => $booking->customer_id,
                                'channel' => 'sms',
                                'message' => $parsedConfirmation,
                                'status' => 'pending',
                                'sent_at' => null,
                            ]);
                            \App\Models\AuditTrail::log($msgLog, 'create', 'MessageLogs');

                            Log::info("📝 [STEP 4a] SMS Confirmation Message queued for Booking #{$booking->id} (Phone: {$booking->customer->phone}, Shop #{$booking->barber_shop_id})");
                        }

                        // 2. Scheduled Reminder SMS (Pending until reminder hours before appointment)
                        $shop = $booking->barberShop;
                        if ($booking->appointment_at) {
                            $rawVal = $shop?->reminder_hours_before;
                            $reminderMins = 30; // Default minimum 30 minutes before appointment if unconfigured

                            if ($rawVal !== null && is_numeric($rawVal) && (float)$rawVal > 0) {
                                $num = (float) $rawVal;
                                if ($num <= 12) {
                                    $reminderMins = (int) round($num * 60);
                                } else {
                                    $reminderMins = (int) round($num);
                                }
                            }

                            $reminderMins = max(15, $reminderMins);

                            $scheduledReminderTime = $booking->appointment_at->copy()->subMinutes($reminderMins);
                            if ($scheduledReminderTime->isFuture()) {
                                $parsedReminder = MessageTemplate::parseForBooking($booking, 'reminder');

                                $existingReminder = MessageQueue::where('booking_id', $booking->id)
                                    ->where('channel', 'sms')
                                    ->where('message_content', $parsedReminder)
                                    ->exists();

                                if (!$existingReminder) {
                                    $reminderQueue = MessageQueue::create([
                                        'barber_shop_id' => $booking->barber_shop_id,
                                        'booking_id' => $booking->id,
                                        'channel' => 'sms',
                                        'phone_number' => $formattedPhone,
                                        'message_content' => $parsedReminder,
                                        'scheduled_at' => $scheduledReminderTime,
                                        'status' => 'pending',
                                        'retry_count' => 0,
                                    ]);
                                    \App\Models\AuditTrail::log($reminderQueue, 'create', 'MessageQueues');

                                    $reminderLog = MessageLog::create([
                                        'barber_shop_id' => $booking->barber_shop_id,
                                        'customer_id' => $booking->customer_id,
                                        'channel' => 'sms',
                                        'message' => $parsedReminder,
                                        'status' => 'pending',
                                        'sent_at' => null,
                                    ]);
                                    \App\Models\AuditTrail::log($reminderLog, 'create', 'MessageLogs');

                                    Log::info("⏰ [STEP 4a] SMS Reminder Message scheduled for Booking #{$booking->id} at {$scheduledReminderTime} (Shop #{$booking->barber_shop_id})");
                                }
                            }
                        }

                        // Find Gateway device for this shop OR user of this shop
                        $gatewayDevice = DeviceToken::where('is_sms_gateway', true)
                            ->where(function($q) use ($shopId) {
                                $q->where('barber_shop_id', $shopId)
                                  ->orWhereHas('user', function($userQuery) use ($shopId) {
                                      $userQuery->where('barber_shop_id', $shopId);
                                  });
                            })
                            ->first();

                        // Global fallback if no shop-specific gateway device found
                        if (!$gatewayDevice || !$gatewayDevice->fcm_token) {
                            $gatewayDevice = DeviceToken::where('is_sms_gateway', true)->whereNotNull('fcm_token')->first();
                            if ($gatewayDevice) {
                                Log::info("ℹ️ [SMS Gateway Fallback] Using global active SMS Gateway device #{$gatewayDevice->id} ({$gatewayDevice->device_name}) for Shop #{$shopId}");
                            }
                        }

                        if ($gatewayDevice && $gatewayDevice->fcm_token) {
                            $fcmSent = $this->firebaseService->sendDataMessage(
                                $gatewayDevice->fcm_token,
                                [
                                    'action' => 'SEND_SMS',
                                    'sms_id' => (string) $queue->id,
                                    'phone' => (string) $booking->customer->phone,
                                    'body' => (string) $parsedConfirmation,
                                ]
                            );
                            Log::info("📱 [STEP 4b] Triggered silent SEND_SMS FCM data message directly to gateway device [{$gatewayDevice->device_name}] (Token: {$gatewayDevice->fcm_token}) for shop #{$shopId}. FCM Status: " . ($fcmSent ? 'SUCCESS' : 'FAILED'));
                        } else {
                            Log::warning("⚠️ [STEP 4b] No active SMS Gateway device found in DB for Shop #{$shopId}. SMS queued as pending.");
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
                Log::info("🔔 [STEP 5] FCM Push Notification sent for shop #{$shopId} event [{$event->event}] to {$sentCount} devices. Title: '{$title}'");
            } else {
                $sentCount = $this->firebaseService->sendToAllDevices($title, $body);
                Log::info("🔔 [STEP 5] FCM Push Notification sent globally for event [{$event->event}] to {$sentCount} devices. Title: '{$title}'");
            }
        } catch (\Throwable $e) {
            Log::error("❌ [STEP ERROR] FCM Push Notification Listener Error for event [{$event->event}]: " . $e->getMessage());
        }
    }

    private function formatPhone(string $raw): string
    {
        $digits = preg_replace('/[^\d]/', '', $raw);
        if (empty($digits)) return $raw;

        if (str_starts_with($digits, '00355')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = '355' . substr($digits, 1);
        } elseif (!str_starts_with($digits, '355')) {
            $digits = '355' . $digits;
        }

        return '+' . $digits;
    }
}
