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
    protected $description = 'Dispatch pending SMS & WhatsApp messages from queue to Gateway devices based on schedule';

    public function handle()
    {
        $now = Carbon::now();

        // 1. Delete past appointment queue items whose appointment time has already passed
        $pastAppointmentQueues = MessageQueue::where('status', 'pending')
            ->whereHas('booking', function($bQuery) use ($now) {
                $bQuery->where('appointment_at', '<', $now);
            })
            ->get();

        foreach ($pastAppointmentQueues as $past) {
            $past->delete();
            Log::info("🧹 [SendQueuedSmsMessages] Deleted past appointment Queue ID #{$past->id}");
        }

        // 2. Retrieve valid pending messages scheduled for NOW or EARLIER
        $pendingMessages = MessageQueue::where('status', 'pending')
            ->where('scheduled_at', '<=', $now)
            ->whereHas('barberShop', function($q) {
                $q->where('active', true);
            })
            ->orderBy('scheduled_at', 'asc')
            ->get();

        if ($pendingMessages->isEmpty()) {
            $this->info("No pending messages to send at {$now}.");
            return;
        }

        $this->info("Found {$pendingMessages->count()} valid pending messages.");

        foreach ($pendingMessages as $index => $msg) {
            try {
                SendSmsToGatewayJob::dispatch($msg->id);
                $this->line("Dispatched Job for Queue ID: {$msg->id} (Channel: {$msg->channel})");

                if ($index < $pendingMessages->count() - 1) {
                    sleep(2);
                }
            } catch (\Exception $e) {
                Log::error("Failed to dispatch message job {$msg->id}: " . $e->getMessage());
            }
        }

        $this->info("Done dispatching.");
    }
}
