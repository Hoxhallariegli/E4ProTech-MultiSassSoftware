<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MessageQueue;
use App\Jobs\SendSmsToGatewayJob;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendQueuedSmsMessages extends Command
{
    protected $signature = 'sms:send-queued';
    protected $description = 'Dispatch pending SMS from queue to Gateway devices with anti-spam rate limiting and stale message auto-purge';

    public function handle()
    {
        $now = Carbon::now();

        // 1. Purge stale/expired messages older than 2 hours OR for shops with sms_enabled == false
        $staleMessages = MessageQueue::where('status', 'pending')
            ->where('channel', 'sms')
            ->where(function($q) use ($now) {
                $q->where('scheduled_at', '<', $now->copy()->subHours(2))
                  ->orWhereHas('barberShop', function($shopQuery) {
                      $shopQuery->where('sms_enabled', false)->orWhere('active', false);
                  });
            })
            ->get();

        foreach ($staleMessages as $stale) {
            $stale->update([
                'status' => 'cancelled',
            ]);
            Log::info("🧹 [SendQueuedSmsMessages] Auto-cancelled stale/disabled SMS Queue ID #{$stale->id}");
        }

        // 2. Retrieve valid pending messages scheduled for now or earlier
        $pendingMessages = MessageQueue::where('status', 'pending')
            ->where('channel', 'sms')
            ->where('scheduled_at', '<=', $now)
            ->whereHas('barberShop', function($q) {
                $q->where('sms_enabled', true)->where('active', true);
            })
            ->orderBy('scheduled_at', 'asc')
            ->get();

        if ($pendingMessages->isEmpty()) {
            $this->info("No pending SMS messages to send at {$now}.");
            return;
        }

        $this->info("Found {$pendingMessages->count()} valid pending SMS messages.");

        foreach ($pendingMessages as $index => $msg) {
            try {
                SendSmsToGatewayJob::dispatch($msg->id);
                $this->line("Dispatched Job for SMS Queue ID: {$msg->id}");

                // Pacing delay (3 seconds between consecutive dispatches) to prevent Android anti-spam SMS rate-limit blocks
                if ($index < $pendingMessages->count() - 1) {
                    sleep(3);
                }
            } catch (\Exception $e) {
                Log::error("Failed to dispatch SMS job {$msg->id}: " . $e->getMessage());
            }
        }

        $this->info("Done dispatching.");
    }
}
