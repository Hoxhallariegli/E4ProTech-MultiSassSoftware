<?php

namespace App\Listeners;

use App\Events\BookingChanged;
use App\Models\Booking;
use App\Models\MessageQueue;
use App\Models\MessageLog;
use App\Models\MessageTemplate;
use App\Models\DeviceToken;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Log;

class SendFirebaseNotificationListener
{
    protected FirebaseService $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function handle(object $event): void
    {
        try {
            $booking = null;
            $action = $event->action ?? 'created';

            if (isset($event->item) && $event->item instanceof Booking) {
                $booking = $event->item;
            } elseif (isset($event->modelClass) && ($event->modelClass === Booking::class || is_a($event->modelClass, Booking::class, true))) {
                $booking = Booking::find($event->modelId);
            }

            if ($booking) {
                $booking->loadMissing(['customer', 'service', 'barber', 'barberShop']);
                $shopId = $booking->barber_shop_id;
                $customerName = $booking->customer?->name ?? 'Klient';
                $serviceName = $booking->service?->name ?? 'Shërbim';
                $barberName = $booking->barber?->name ?? 'Staf';
                $shopName = $booking->barberShop?->name ?? 'Sallon';
                $time = $booking->appointment_at ? $booking->appointment_at->format('H:i d/m/Y') : '';

                $title = "Rezervim i Ri ($shopName)";

                // Custom Push Notification Body
                if ($action === 'created') {
                    $body = "Klienti {$customerName} rezervoi {$serviceName} për orën {$time} tek {$barberName}.";
                } elseif ($action === 'updated') {
                    $body = "Rezervimi i {$customerName} u përditësua për {$time}.";
                } else {
                    $body = "Ka një përditësim te rezervimi i {$customerName}.";
                }

                // Queue SMS confirmation AND scheduled reminder when booking is newly created and send_sms is true
                $sendSmsParam = request()->input('send_sms');
                $sendSmsRequested = ($sendSmsParam !== null && $sendSmsParam !== '')
                    ? filter_var($sendSmsParam, FILTER_VALIDATE_BOOLEAN)
                    : true;

                Log::info("📌 [SMS QUEUE CHECK] Booking #{$booking->id} Action: {$action}, SendSMS Requested: " . ($sendSmsRequested ? 'YES' : 'NO') . ", Has Customer: " . ($booking->customer ? 'YES' : 'NO') . ", Customer Phone: " . ($booking->customer?->phone ?? 'NONE'));

                if ($action === 'created' && $booking->customer && $booking->customer->phone && $sendSmsRequested) {
                    $parsedConfirmation = MessageTemplate::parseForBooking($booking, 'confirmation');
                    $formattedPhone = $this->formatPhone($booking->customer->phone);

                    // 1. Direct Confirmation SMS (Immediate) - Check if already created
                    $existingConfirmation = MessageQueue::where('booking_id', $booking->id)
                        ->where('channel', 'sms')
                        ->where('template_type', 'confirmation')
                        ->first();

                    $queue = $existingConfirmation;

                    if (!$queue) {
                        $queue = MessageQueue::create([
                            'barber_shop_id' => $booking->barber_shop_id,
                            'booking_id' => $booking->id,
                            'channel' => 'sms',
                            'template_type' => 'confirmation',
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
                            'template_type' => 'confirmation',
                            'message' => $parsedConfirmation,
                            'status' => 'pending',
                            'sent_at' => null,
                        ]);
                        \App\Models\AuditTrail::log($msgLog, 'create', 'MessageLogs');

                        Log::info("📝 [STEP 4a] SMS Confirmation Message queued for Booking #{$booking->id} (Phone: {$booking->customer->phone}, Shop #{$booking->barber_shop_id})");
                    }

                    // 2. Scheduled Reminder SMS (Pending until reminder hours before appointment)
                    $shop = $booking->barberShop;
                    if ($booking->appointment_at && $booking->appointment_at->isFuture()) {
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

                        $reminderMins = max(10, $reminderMins);

                        $scheduledReminderTime = $booking->appointment_at->copy()->subMinutes($reminderMins);
                        if ($scheduledReminderTime->isPast()) {
                            $scheduledReminderTime = now();
                        }

                        $parsedReminder = MessageTemplate::parseForBooking($booking, 'reminder');

                        $existingReminder = MessageQueue::where('booking_id', $booking->id)
                            ->where('channel', 'sms')
                            ->where('template_type', 'reminder')
                            ->exists();

                        if (!$existingReminder) {
                            $reminderQueue = MessageQueue::create([
                                'barber_shop_id' => $booking->barber_shop_id,
                                'booking_id' => $booking->id,
                                'channel' => 'sms',
                                'template_type' => 'reminder',
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
                                'template_type' => 'reminder',
                                'message' => $parsedReminder,
                                'status' => 'pending',
                                'sent_at' => null,
                            ]);
                            \App\Models\AuditTrail::log($reminderLog, 'create', 'MessageLogs');

                            Log::info("⏰ [STEP 4a] SMS Reminder Message scheduled for Booking #{$booking->id} at {$scheduledReminderTime} (Shop #{$booking->barber_shop_id})");
                        }
                    }

                    // Find active SMS Gateway device for this shop or shop owner
                    $gatewayDevice = DeviceToken::where('is_sms_gateway', true)
                        ->whereNotNull('fcm_token')
                        ->where(function($q) use ($shopId) {
                            $q->where('barber_shop_id', $shopId)
                              ->orWhereHas('user', function($userQuery) use ($shopId) {
                                  $userQuery->where('barber_shop_id', $shopId)
                                            ->orWhereHas('roles', fn($roleQuery) => $roleQuery->where('name', 'admin'));
                              });
                        })
                        ->orderByDesc('last_used_at')
                        ->first();

                    if ($gatewayDevice && $gatewayDevice->fcm_token) {
                        $fcmSent = $this->firebaseService->sendDataMessage(
                            $gatewayDevice->fcm_token,
                            [
                                'action' => 'SEND_SMS',
                                'sms_id' => (string) ($queue?->id ?? 0),
                                'phone' => (string) $booking->customer->phone,
                                'body' => (string) $parsedConfirmation,
                            ]
                        );
                        Log::info("📱 [STEP 4b] Triggered silent SEND_SMS FCM data message directly to gateway device #{$gatewayDevice->id} [{$gatewayDevice->device_name}] for Shop #{$shopId}. FCM Status: " . ($fcmSent ? 'SUCCESS' : 'FAILED'));
                    } else {
                        Log::warning("⚠️ [STEP 4b] No active SMS Gateway device found in DB for Shop #{$shopId}. SMS queued as pending for Shop #{$shopId}.");
                    }
                }

                // Dispatch FCM Push Notification STRICTLY ISOLATED PER SHOP ID!
                $customData = [
                    'shop_id' => (string) ($shopId ?? 0),
                    'action' => (string) $action,
                    'model' => 'Booking',
                    'model_id' => (string) $booking->id,
                ];

                if ($shopId) {
                    $sentCount = $this->firebaseService->sendToShop($shopId, $title, $body, $customData);
                    Log::info("🔔 [STEP 5] FCM Push Notification sent for shop #{$shopId} event [bookings.{$action}] to {$sentCount} devices. Title: '{$title}'");
                }
            }
        } catch (\Throwable $e) {
            Log::error("❌ [STEP ERROR] FCM Push Notification Listener Error: " . $e->getMessage());
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
