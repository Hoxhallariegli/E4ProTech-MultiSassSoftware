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

        $shopId = $queue->barber_shop_id;

        // 1. First look for shop-specific or user-specific gateway device
        $gatewayDevice = DeviceToken::where('is_sms_gateway', true)
            ->where(function($q) use ($shopId) {
                $q->where('barber_shop_id', $shopId)
                  ->orWhereHas('user', function($userQuery) use ($shopId) {
                      $userQuery->where('barber_shop_id', $shopId);
                  });
            })
            ->first();

        // 2. Global fallback to ANY active SMS Gateway device if shop-specific isn't found
        if (!$gatewayDevice || !$gatewayDevice->fcm_token) {
            $gatewayDevice = DeviceToken::where('is_sms_gateway', true)->whereNotNull('fcm_token')->first();
            if ($gatewayDevice) {
                Log::info("ℹ️ [SMS Gateway Fallback] Using global active SMS Gateway device #{$gatewayDevice->id} ({$gatewayDevice->device_name}) for Shop #{$shopId}");
            }
        }

        if (!$gatewayDevice || !$gatewayDevice->fcm_token) {
            $queue->increment('retry_count');
            Log::info("⚠️ [SMS Gateway Pending] No active SMS Gateway device found in system for Shop #{$shopId}. Queue #{$queue->id} waiting.");
            return; // Return gracefully without throwing an exception to avoid log spam
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
