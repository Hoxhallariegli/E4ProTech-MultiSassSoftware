<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\MessageQueue;
use App\Models\DeviceToken;
use App\Services\FirebaseService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class SendSmsToGatewayJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120];

    protected $messageQueueId;

    public function __construct($messageQueueId)
    {
        $this->messageQueueId = $messageQueueId;
    }

    public function handle(FirebaseService $firebaseService): void
    {
        $queue = MessageQueue::find($this->messageQueueId);

        if (!$queue || in_array($queue->status, ['sent', 'failed', 'deleted'])) {
            return;
        }

        // Delete queue item ONLY if the booking's appointment time has already passed
        if ($queue->booking && $queue->booking->appointment_at && $queue->booking->appointment_at->isPast()) {
            $queue->delete();
            Log::info("🧹 [SendSmsToGatewayJob] Deleted past appointment SMS Queue ID #{$queue->id}");
            return;
        }

        $shopId = $queue->barber_shop_id;
        $booking = $queue->booking;
        $barberId = $booking?->barber_id;
        $customerId = $booking?->customer_id;
        $templateType = $queue->resolved_template_type;

        // 1. TIER 1 & TIER 2: Try sending via WhatsApp (Barber WhatsApp -> Shop WhatsApp -> Default WhatsApp)
        try {
            $waSent = app(WhatsAppService::class)->send(
                $queue->phone_number,
                $queue->message_content,
                $shopId,
                $barberId,
                $customerId,
                $templateType
            );

            if ($waSent) {
                $queue->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'channel' => 'whatsapp',
                ]);
                Log::info("💬 [SendSmsToGatewayJob] Scheduled reminder ID #{$queue->id} successfully sent via WhatsApp Gateway.");
                return;
            }
        } catch (\Throwable $e) {
            Log::warning("⚠️ [SendSmsToGatewayJob] WhatsApp Gateway attempt failed for Queue ID #{$queue->id}: " . $e->getMessage());
        }

        // 2. TIER 3: Fallback to SIM Card SMS Gateway device
        $gatewayDevice = DeviceToken::where('is_sms_gateway', true)
            ->where(function($q) use ($shopId) {
                if ($shopId) {
                    $q->where('barber_shop_id', $shopId);
                }
            })
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

        if (!$gatewayDevice || !$gatewayDevice->fcm_token) {
            Log::info("⚠️ [SMS Gateway Pending] No active SIM Gateway or WhatsApp Gateway available for Shop #{$shopId}. Queue #{$queue->id} waiting.");
            return;
        }

        try {
            $fcmSent = $firebaseService->sendDataMessage(
                $gatewayDevice->fcm_token,
                [
                    'action' => 'SEND_SMS',
                    'type' => 'SEND_SMS',
                    'sms_id' => (string) $queue->id,
                    'phone' => (string) $queue->phone_number,
                    'body' => (string) $queue->message_content,
                    'message' => (string) $queue->message_content,
                    'timestamp' => (string) now()->timestamp,
                ]
            );

            Log::info("📱 [SendSmsToGatewayJob] Pushed silent SEND_SMS FCM data message to Gateway device [{$gatewayDevice->device_name}] for Queue ID: {$queue->id}. FCM Status: " . ($fcmSent ? 'SUCCESS' : 'FAILED'));

        } catch (\Throwable $e) {
            $queue->increment('retry_count');
            Log::error("❌ [SendSmsToGatewayJob Error] Failed to push SMS to Gateway: " . $e->getMessage());
        }
    }
}
