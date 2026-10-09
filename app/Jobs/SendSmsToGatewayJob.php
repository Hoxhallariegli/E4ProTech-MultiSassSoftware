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

        if (!$queue || $queue->status !== 'pending') {
            return;
        }

        $now = now();

        // 1. Auto-cancel expired/past messages older than 15 minutes
        if ($queue->scheduled_at && $queue->scheduled_at->isBefore($now->copy()->subMinutes(15))) {
            $queue->update(['status' => 'failed']);
            Log::info("🧹 [SendSmsToGatewayJob] Cancelled expired SMS Queue ID #{$queue->id} (scheduled_at {$queue->scheduled_at} was in the past).");
            return;
        }

        if ($queue->booking && $queue->booking->appointment_at && $queue->booking->appointment_at->isBefore($now->copy()->subMinutes(15))) {
            $queue->update(['status' => 'failed']);
            Log::info("🧹 [SendSmsToGatewayJob] Cancelled expired SMS Queue ID #{$queue->id} (appointment_at {$queue->booking->appointment_at} was in the past).");
            return;
        }

        $shopId = $queue->barber_shop_id;

        // 2. Strict lookup for active SMS Gateway device assigned to this shop
        $gatewayDevice = DeviceToken::where('barber_shop_id', $shopId)
            ->where('is_sms_gateway', true)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->where('fcm_token', '!=', 'inactive_token')
            ->orderByDesc('last_used_at')
            ->first();

        if (!$gatewayDevice || !$gatewayDevice->fcm_token) {
            Log::info("⚠️ [SMS Gateway Pending] No active SMS Gateway device found in DB for Shop #{$shopId}. Queue #{$queue->id} waiting.");
            return;
        }

        try {
            $fcmSent = $firebaseService->sendDataMessage(
                $gatewayDevice->fcm_token,
                [
                    'action' => 'SEND_SMS',
                    'sms_id' => (string) $queue->id,
                    'phone' => (string) $queue->phone_number,
                    'body' => (string) $queue->message_content,
                ]
            );

            // Mark as 'processing' in Queue
            $queue->update(['status' => 'processing']);
            Log::info("📱 [SendSmsToGatewayJob] Pushed silent SEND_SMS FCM data message to Gateway device [{$gatewayDevice->device_name}] for Queue ID: {$queue->id}. FCM Status: " . ($fcmSent ? 'SUCCESS' : 'FAILED'));

        } catch (\Throwable $e) {
            $queue->increment('retry_count');
            Log::error("❌ [SendSmsToGatewayJob Error] Failed to push SMS to Gateway: " . $e->getMessage());
        }
    }
}
