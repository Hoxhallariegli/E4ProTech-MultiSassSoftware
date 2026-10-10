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
use Illuminate\Support\Facades\Schema;

class SendFirebaseNotificationListener
{
    protected FirebaseService $firebaseService;
    protected static array $processedBookings = [];

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
                $dedupKey = "booking_sms:{$booking->id}:{$action}";
                if (isset(static::$processedBookings[$dedupKey])) {
                    Log::info("⏭️ [SendFirebaseNotificationListener] Skipped duplicate execution for {$dedupKey}");
                    return;
                }
                static::$processedBookings[$dedupKey] = true;
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

                // Queue SMS confirmation AND scheduled reminder
                $source = $booking->source ?? 'online';
                $sendSmsParam = request()->input('send_sms');

                if ($sendSmsParam !== null && $sendSmsParam !== '') {
                    $sendSmsRequested = filter_var($sendSmsParam, FILTER_VALIDATE_BOOLEAN);
                } else {
                    // For online public bookings, default send_sms is true.
                    // For manual staff walk-in bookings created in APK/Admin, send_sms defaults to false unless send_sms = true is passed!
                    $sendSmsRequested = ($source === 'online');
                }

                Log::info("📌 [SMS QUEUE CHECK] Booking #{$booking->id} (Source: {$source}) Action: {$action}, SendSMS Requested: " . ($sendSmsRequested ? 'YES' : 'NO') . ", Customer Phone: " . ($booking->customer?->phone ?? 'NONE'));

                // Check if shop has SMS enabled
                $shop = $booking->barberShop;
                $shopSmsEnabled = $shop ? (bool)$shop->sms_enabled : true;

                if (!$shopSmsEnabled) {
                    Log::info("⏸️ [STEP 4] SMS is disabled for Shop #{$shopId} (sms_enabled = false). Skipping SMS queue and trigger.");
                    return;
                }

                if ($booking->customer && $booking->customer->phone && $sendSmsRequested) {
                    $formattedPhone = $this->formatPhone($booking->customer->phone);

                    // -------------------------------------------------------------
                    // ACTION A: CREATED -> Send Confirmation + Schedule Reminder
                    // -------------------------------------------------------------
                    if ($action === 'created') {
                        $parsedConfirmation = MessageTemplate::parseForBooking($booking, 'confirmation');

                        // Fetch existing SMS queue items for this booking
                        $existingSmsQueues = MessageQueue::where('booking_id', $booking->id)
                            ->where('channel', 'sms')
                            ->get();

                        // 1. Direct Confirmation SMS (Immediate)
                        $existingConfirmation = $existingSmsQueues->first(fn($q) => ($q->template_type ?? $q->resolved_template_type) === 'confirmation');
                        $queue = $existingConfirmation;

                        if (!$queue) {
                            $queueData = [
                                'barber_shop_id' => $booking->barber_shop_id,
                                'booking_id' => $booking->id,
                                'channel' => 'sms',
                                'phone_number' => $formattedPhone,
                                'message_content' => $parsedConfirmation,
                                'scheduled_at' => now(),
                                'status' => 'pending',
                                'retry_count' => 0,
                            ];
                            if (Schema::hasColumn('message_queues', 'template_type')) {
                                $queueData['template_type'] = 'confirmation';
                            }
                            $queue = MessageQueue::create($queueData);
                            \App\Models\AuditTrail::log($queue, 'create', 'MessageQueues');

                            $logData = [
                                'barber_shop_id' => $booking->barber_shop_id,
                                'customer_id' => $booking->customer_id,
                                'channel' => 'sms',
                                'message' => $parsedConfirmation,
                                'status' => 'pending',
                                'sent_at' => null,
                            ];
                            if (Schema::hasColumn('message_logs', 'template_type')) {
                                $logData['template_type'] = 'confirmation';
                            }
                            $msgLog = MessageLog::create($logData);
                            \App\Models\AuditTrail::log($msgLog, 'create', 'MessageLogs');

                            Log::info("📝 [STEP 4a] SMS Confirmation Message queued for Booking #{$booking->id} (Phone: {$booking->customer->phone}, Shop #{$booking->barber_shop_id})");
                        }

                        // 2. Scheduled Reminder SMS (Pending until reminder hours before appointment)
                        $shop = $booking->barberShop;
                        if ($booking->appointment_at) {
                            $rawVal = $shop?->reminder_hours_before;
                            $reminderMins = 30;

                            if ($rawVal !== null && is_numeric($rawVal) && (float)$rawVal > 0) {
                                $num = (float) $rawVal;
                                $reminderMins = ($num <= 12) ? (int) round($num * 60) : (int) round($num);
                            }
                            $reminderMins = max(10, $reminderMins);

                            $scheduledReminderTime = $booking->appointment_at->copy()->subMinutes($reminderMins);
                            if ($scheduledReminderTime->isPast()) {
                                $scheduledReminderTime = now();
                            }

                            $parsedReminder = MessageTemplate::parseForBooking($booking, 'reminder');
                            $existingReminder = $existingSmsQueues->first(fn($q) => ($q->template_type ?? $q->resolved_template_type) === 'reminder');

                            if (!$existingReminder) {
                                $reminderQueueData = [
                                    'barber_shop_id' => $booking->barber_shop_id,
                                    'booking_id' => $booking->id,
                                    'channel' => 'sms',
                                    'phone_number' => $formattedPhone,
                                    'message_content' => $parsedReminder,
                                    'scheduled_at' => $scheduledReminderTime,
                                    'status' => 'pending',
                                    'retry_count' => 0,
                                ];
                                if (Schema::hasColumn('message_queues', 'template_type')) {
                                    $reminderQueueData['template_type'] = 'reminder';
                                }
                                $reminderQueue = MessageQueue::create($reminderQueueData);
                                \App\Models\AuditTrail::log($reminderQueue, 'create', 'MessageQueues');

                                $reminderLogData = [
                                    'barber_shop_id' => $booking->barber_shop_id,
                                    'customer_id' => $booking->customer_id,
                                    'channel' => 'sms',
                                    'message' => $parsedReminder,
                                    'status' => 'pending',
                                    'sent_at' => null,
                                ];
                                if (Schema::hasColumn('message_logs', 'template_type')) {
                                    $reminderLogData['template_type'] = 'reminder';
                                }
                                $reminderLog = MessageLog::create($reminderLogData);
                                \App\Models\AuditTrail::log($reminderLog, 'create', 'MessageLogs');

                                Log::info("⏰ [STEP 4a] SMS Reminder Message scheduled for Booking #{$booking->id} at {$scheduledReminderTime} (Shop #{$booking->barber_shop_id})");
                            }
                        }

                        // Trigger SMS Gateway Device
                        $this->triggerGatewayDevice($shopId, $queue?->id ?? 0, $booking->customer->phone, $parsedConfirmation);
                    }

                    // -------------------------------------------------------------
                    // ACTION B: UPDATED -> Reschedule Pending Reminder + Send Update SMS
                    // -------------------------------------------------------------
                    elseif ($action === 'updated') {
                        $shop = $booking->barberShop;

                        // 1. Reschedule and update pending reminder SMS
                        if ($booking->appointment_at) {
                            $rawVal = $shop?->reminder_hours_before;
                            $reminderMins = 30;
                            if ($rawVal !== null && is_numeric($rawVal) && (float)$rawVal > 0) {
                                $num = (float) $rawVal;
                                $reminderMins = ($num <= 12) ? (int) round($num * 60) : (int) round($num);
                            }
                            $reminderMins = max(10, $reminderMins);

                            $scheduledReminderTime = $booking->appointment_at->copy()->subMinutes($reminderMins);
                            if ($scheduledReminderTime->isPast()) {
                                $scheduledReminderTime = now();
                            }

                            $parsedReminder = MessageTemplate::parseForBooking($booking, 'reminder');

                            // Find and update existing pending reminder in MessageQueue
                            $pendingReminder = MessageQueue::where('booking_id', $booking->id)
                                ->where('channel', 'sms')
                                ->where('status', 'pending')
                                ->where(function($q) {
                                    $q->where('template_type', 'reminder')
                                      ->orWhere('message_content', 'like', '%Rikujtese%')
                                      ->orWhere('message_content', 'like', '%Reminder%');
                                })
                                ->first();

                            if ($pendingReminder) {
                                $pendingReminder->update([
                                    'scheduled_at' => $scheduledReminderTime,
                                    'message_content' => $parsedReminder,
                                    'phone_number' => $formattedPhone,
                                ]);
                                Log::info("⏰ [RESCHEDULE] Pending SMS Reminder for Booking #{$booking->id} updated to {$scheduledReminderTime} with new content.");
                            }
                        }

                        // 2. Queue and Send Immediate Update / Reschedule SMS
                        $parsedUpdate = MessageTemplate::parseForBooking($booking, 'reschedule');

                        $updateQueueData = [
                            'barber_shop_id' => $booking->barber_shop_id,
                            'booking_id' => $booking->id,
                            'channel' => 'sms',
                            'phone_number' => $formattedPhone,
                            'message_content' => $parsedUpdate,
                            'scheduled_at' => now(),
                            'status' => 'pending',
                            'retry_count' => 0,
                        ];
                        if (Schema::hasColumn('message_queues', 'template_type')) {
                            $updateQueueData['template_type'] = 'reschedule';
                        }
                        $updateQueue = MessageQueue::create($updateQueueData);
                        \App\Models\AuditTrail::log($updateQueue, 'create', 'MessageQueues');

                        $updateLogData = [
                            'barber_shop_id' => $booking->barber_shop_id,
                            'customer_id' => $booking->customer_id,
                            'channel' => 'sms',
                            'message' => $parsedUpdate,
                            'status' => 'pending',
                            'sent_at' => null,
                        ];
                        if (Schema::hasColumn('message_logs', 'template_type')) {
                            $updateLogData['template_type'] = 'reschedule';
                        }
                        $updateLog = MessageLog::create($updateLogData);
                        \App\Models\AuditTrail::log($updateLog, 'create', 'MessageLogs');

                        Log::info("🔄 [STEP 4a-UPDATE] SMS Update Message queued for Booking #{$booking->id} (Phone: {$booking->customer->phone}, Shop #{$booking->barber_shop_id})");

                        // Trigger Gateway Device
                        $this->triggerGatewayDevice($shopId, $updateQueue->id, $booking->customer->phone, $parsedUpdate);
                    }

                    // -------------------------------------------------------------
                    // ACTION C: DELETED -> Cancel Pending Reminder + Send Cancellation SMS
                    // -------------------------------------------------------------
                    elseif ($action === 'deleted') {
                        // Cancel pending reminders for this booking
                        MessageQueue::where('booking_id', $booking->id)
                            ->where('status', 'pending')
                            ->delete();

                        $parsedCancel = MessageTemplate::parseForBooking($booking, 'cancellation');

                        $cancelQueueData = [
                            'barber_shop_id' => $booking->barber_shop_id,
                            'booking_id' => $booking->id,
                            'channel' => 'sms',
                            'phone_number' => $formattedPhone,
                            'message_content' => $parsedCancel,
                            'scheduled_at' => now(),
                            'status' => 'pending',
                            'retry_count' => 0,
                        ];
                        if (Schema::hasColumn('message_queues', 'template_type')) {
                            $cancelQueueData['template_type'] = 'cancellation';
                        }
                        $cancelQueue = MessageQueue::create($cancelQueueData);

                        Log::info("❌ [STEP 4a-CANCEL] SMS Cancellation Message queued for Booking #{$booking->id}");

                        // Trigger Gateway Device
                        $this->triggerGatewayDevice($shopId, $cancelQueue->id, $booking->customer->phone, $parsedCancel);
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

    private function triggerGatewayDevice(int $shopId, int $smsId, string $phone, string $body): void
    {
        $gatewayDevice = DeviceToken::where('barber_shop_id', $shopId)
            ->where('is_sms_gateway', true)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->where('fcm_token', '!=', 'inactive_token')
            ->orderByDesc('last_used_at')
            ->first();

        if ($gatewayDevice && $gatewayDevice->fcm_token) {
            $fcmSent = $this->firebaseService->sendDataMessage(
                $gatewayDevice->fcm_token,
                [
                    'action' => 'SEND_SMS',
                    'sms_id' => (string) $smsId,
                    'phone' => (string) $phone,
                    'body' => (string) $body,
                ]
            );
            Log::info("📱 [TRIGGER GATEWAY] SEND_SMS FCM data message sent to gateway device #{$gatewayDevice->id} [{$gatewayDevice->device_name}] for Shop #{$shopId}. FCM Status: " . ($fcmSent ? 'SUCCESS' : 'FAILED'));
        } else {
            Log::warning("⚠️ [TRIGGER GATEWAY] No active SMS Gateway device found in DB for Shop #{$shopId}. SMS queued as pending.");
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
