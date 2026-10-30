<?php

namespace App\Listeners;

use App\Events\BookingChanged;
use App\Models\Booking;
use App\Models\BarberShop;
use App\Models\Barber;
use App\Models\MessageQueue;
use App\Models\MessageLog;
use App\Models\MessageTemplate;
use App\Models\DeviceToken;
use App\Services\FirebaseService;
use App\Services\WhatsAppService;
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

                // Check SMS request flag
                $source = $booking->source ?? 'online';
                $sendSmsParam = request()->input('send_sms');

                if ($sendSmsParam !== null && $sendSmsParam !== '') {
                    $sendSmsRequested = filter_var($sendSmsParam, FILTER_VALIDATE_BOOLEAN);
                } else {
                    $sendSmsRequested = true;
                }

                Log::info("📌 [SMS QUEUE CHECK] Booking #{$booking->id} (Source: {$source}) Action: {$action}, SendSMS Requested: " . ($sendSmsRequested ? 'YES' : 'NO') . ", Customer Phone: " . ($booking->customer?->phone ?? 'NONE'));

                // Check if shop or barber has SMS / WhatsApp enabled
                $shop = $booking->barberShop;
                $barber = $booking->barber;
                $shopSmsEnabled = $shop ? (bool)($shop->sms_enabled ?? true) : true;

                if (!$shopSmsEnabled && !($shop?->whatsapp_enabled ?? true) && !($barber?->whatsapp_enabled ?? true)) {
                    Log::info("⏸️ [STEP 4] Notifications are disabled for Shop #{$shopId}. Skipping.");
                    return;
                }

                if ($booking->customer && $booking->customer->phone && $sendSmsRequested) {
                    $formattedPhone = $this->formatPhone($booking->customer->phone);
                    $preferredChannel = ($barber?->whatsapp_enabled ?? true) || ($shop?->whatsapp_enabled ?? true) ? 'whatsapp' : 'sms';

                    // -------------------------------------------------------------
                    // ACTION A: CREATED -> Immediate Direct Confirmation + Queue Scheduled Reminder ONLY
                    // -------------------------------------------------------------
                    if ($action === 'created') {
                        $parsedConfirmation = MessageTemplate::parseForBooking($booking, 'confirmation', null, $preferredChannel);

                        // 1. Direct Confirmation Message (Immediate, NO row created in message_queues table!)
                        $this->triggerGatewayDevice($shopId, 0, $formattedPhone, $parsedConfirmation, $booking->barber_id, $booking->customer_id, 'confirmation');
                        Log::info("📝 [STEP 4a] Direct Confirmation Message sent for Booking #{$booking->id} (Phone: {$booking->customer->phone}, Channel: {$preferredChannel})");

                        // 2. Scheduled Reminder Message ONLY inserted into message_queues
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

                            $parsedReminder = MessageTemplate::parseForBooking($booking, 'reminder', null, $preferredChannel);
                            $existingReminder = MessageQueue::where('booking_id', $booking->id)
                                ->where(function($q) {
                                    $q->where('template_type', 'reminder')
                                      ->orWhere('message_content', 'like', '%Rikujtes%')
                                      ->orWhere('message_content', 'like', '%Reminder%');
                                })->exists();

                            if (!$existingReminder) {
                                $reminderQueueData = [
                                    'barber_shop_id' => $booking->barber_shop_id,
                                    'booking_id' => $booking->id,
                                    'channel' => $preferredChannel,
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

                                Log::info("⏰ [STEP 4a] Reminder Message scheduled for Booking #{$booking->id} at {$scheduledReminderTime} in MessageQueue");
                            }
                        }
                    }

                    // -------------------------------------------------------------
                    // ACTION B: UPDATED -> Reschedule Pending Reminder + Direct Reschedule SMS
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

                            $parsedReminder = MessageTemplate::parseForBooking($booking, 'reminder', null, $preferredChannel);

                            // Find and update existing pending reminder in MessageQueue
                            $pendingReminder = MessageQueue::where('booking_id', $booking->id)
                                ->where('status', 'pending')
                                ->where(function($q) {
                                    $q->where('template_type', 'reminder')
                                      ->orWhere('message_content', 'like', '%Rikujtes%')
                                      ->orWhere('message_content', 'like', '%Reminder%');
                                })
                                ->first();

                            if ($pendingReminder) {
                                $pendingReminder->update([
                                    'channel' => $preferredChannel,
                                    'scheduled_at' => $scheduledReminderTime,
                                    'message_content' => $parsedReminder,
                                    'phone_number' => $formattedPhone,
                                ]);
                                Log::info("⏰ [RESCHEDULE] Pending Reminder for Booking #{$booking->id} updated to {$scheduledReminderTime} with new content.");
                            }
                        }

                        // 2. Direct Update / Reschedule Message
                        $timeOrDetailsChanged = $booking->wasChanged('appointment_at') || $booking->wasChanged('service_id') || $booking->wasChanged('barber_id');
                        if ($timeOrDetailsChanged) {
                            $parsedUpdate = MessageTemplate::parseForBooking($booking, 'reschedule', null, $preferredChannel);
                            $this->triggerGatewayDevice($shopId, 0, $formattedPhone, $parsedUpdate, $booking->barber_id, $booking->customer_id, 'reschedule');
                        }
                    }

                    // -------------------------------------------------------------
                    // ACTION C: DELETED -> Cancel Pending Reminder + Direct Cancellation SMS
                    // -------------------------------------------------------------
                    elseif ($action === 'deleted') {
                        // Cancel pending reminders for this booking
                        MessageQueue::where('booking_id', $booking->id)
                            ->where('status', 'pending')
                            ->delete();

                        $parsedCancel = MessageTemplate::parseForBooking($booking, 'cancellation', null, $preferredChannel);
                        $this->triggerGatewayDevice($shopId, 0, $formattedPhone, $parsedCancel, $booking->barber_id, $booking->customer_id, 'cancellation');
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

    private function triggerGatewayDevice(int $shopId, int $smsId, string $phone, string $body, ?int $barberId = null, ?int $customerId = null, ?string $templateType = null): void
    {
        // Asynchronous Background Dispatch for 10ms Lightning Fast UI Speed!
        dispatch(function () use ($shopId, $smsId, $phone, $body, $barberId, $customerId, $templateType) {
            $shop = BarberShop::find($shopId);
            $barber = $barberId ? Barber::find($barberId) : null;

            $shopWaEnabled = $shop ? (bool) ($shop->whatsapp_enabled ?? true) : true;
            $barberWaEnabled = $barber ? (bool) ($barber->whatsapp_enabled ?? true) : true;

            $waService = app(WhatsAppService::class);

            $markQueueSent = function() use ($smsId) {
                if ($smsId > 0) {
                    try {
                        MessageQueue::where('id', $smsId)->delete();
                    } catch (\Throwable $e) {
                        Log::warning("Could not delete processed queue item: " . $e->getMessage());
                    }
                }
            };

            // 1. TIER 1: Barber's Personal WhatsApp (Priority #1)
            if ($barberId && $barberWaEnabled) {
                try {
                    $waSent = $waService->send($phone, $body, $shopId, $barberId, $customerId, $templateType);
                    if ($waSent) {
                        $markQueueSent();
                        Log::info("💬 [SMART ROUTE - TIER 1] Sent via Barber #{$barberId} Personal WhatsApp to {$phone}");
                        return; // Successfully delivered via Barber WhatsApp!
                    }
                } catch (\Throwable $e) {
                    Log::warning("⚠️ [SMART ROUTE - TIER 1 FAIL] Barber WhatsApp failed: " . $e->getMessage());
                }
            }

            // 2. TIER 2: Barber Shop's Central WhatsApp (Priority #2)
            if ($shopWaEnabled) {
                try {
                    $waSent = $waService->send($phone, $body, $shopId, null, $customerId, $templateType);
                    if ($waSent) {
                        $markQueueSent();
                        Log::info("💬 [SMART ROUTE - TIER 2] Sent via Shop #{$shopId} Central WhatsApp to {$phone}");
                        return; // Successfully delivered via Shop WhatsApp!
                    }
                } catch (\Throwable $e) {
                    Log::warning("⚠️ [SMART ROUTE - TIER 2 FAIL] Shop WhatsApp failed: " . $e->getMessage());
                }
            }

            // 3. TIER 3: SIM Card SMS Gateway (Priority #3)
            $shopSmsEnabled = $shop ? (bool) ($shop->sms_enabled ?? true) : true;
            if (!$shopSmsEnabled) {
                Log::info("⏸️ [SMART ROUTE - TIER 3 SKIP] SIM SMS is disabled for Shop #{$shopId}. All routes skipped cleanly.");
                return;
            }

            $gatewayDevice = DeviceToken::where('barber_shop_id', $shopId)
                ->where('is_sms_gateway', true)
                ->whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '')
                ->where('fcm_token', '!=', 'inactive_token')
                ->orderByDesc('last_used_at')
                ->first();

            if (!$gatewayDevice) {
                $gatewayDevice = DeviceToken::where('is_sms_gateway', true)
                    ->whereNotNull('fcm_token')
                    ->where('fcm_token', '!=', '')
                    ->where('fcm_token', '!=', 'inactive_token')
                    ->orderByDesc('last_used_at')
                    ->first();
            }

            if ($gatewayDevice && $gatewayDevice->fcm_token) {
                app(FirebaseService::class)->sendDataMessage(
                    $gatewayDevice->fcm_token,
                    [
                        'action' => 'SEND_SMS',
                        'type' => 'SEND_SMS',
                        'sms_id' => (string) $smsId,
                        'phone' => (string) $phone,
                        'body' => (string) $body,
                        'message' => (string) $body,
                    ]
                );
                Log::info("📱 [SMART ROUTE - TIER 3] Sent via SIM SMS Gateway Device [{$gatewayDevice->device_name}] to {$phone}.");
            } else {
                Log::info("⏭️ [SMART ROUTE - TIER 3 SKIP] No active SIM Gateway device found for Shop #{$shopId}. All routes skipped cleanly.");
            }
        })->afterResponse();
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
