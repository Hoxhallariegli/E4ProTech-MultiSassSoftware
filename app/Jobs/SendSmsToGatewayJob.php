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

        $gatewayDevice = DeviceToken::where('barber_shop_id', $queue->barber_shop_id)
            ->where('is_sms_gateway', true)
            ->first();

        if (!$gatewayDevice || !$gatewayDevice->fcm_token) {
            $queue->increment('retry_count');
            Log::warning("No active SMS Gateway found for shop #{$queue->barber_shop_id}. Retrying later.");
            throw new \Exception("SMS Gateway offline or not registered.");
        }

        try {
            $firebaseService->sendNotification(
                'SMS Gateway',
                'Duke dërguar SMS...',
                $gatewayDevice->fcm_token,
                [
                    'action' => 'SEND_SMS',
                    'sms_id' => (string) $queue->id,
                    'phone' => (string) $queue->phone_number,
                    'body' => (string) $queue->message_content,
                ]
            );

            // Mark as 'sent' in Queue but we wait for confirmation in MessageLog
            $queue->update(['status' => 'processing']);
            Log::info("Pushed SEND_SMS to Gateway device for Queue ID: {$queue->id}");

        } catch (\Throwable $e) {
            $queue->increment('retry_count');
            Log::error("Failed to push SMS to Gateway: " . $e->getMessage());
            throw $e;
        }
    }
}
