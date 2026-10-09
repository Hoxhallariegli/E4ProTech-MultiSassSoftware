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
    protected $description = 'Dispatch pending SMS from queue to Gateway devices based on reminder_hours_before schedule';

    public function handle()
    {
        $now = Carbon::now();

        // 1. Delete past appointment SMS queue items whose appointment time has already passed
        $pastAppointmentQueues = MessageQueue::where('status', 'pending')
            ->where('channel', 'sms')
            ->whereHas('booking', function($bQuery) use ($now) {
                $bQuery->where('appointment_at', '<', $now);
            })
            ->get();

        foreach ($pastAppointmentQueues as $past) {
            $past->delete();
            Log::info("🧹 [SendQueuedSmsMessages] Deleted past appointment SMS Queue ID #{$past->id}");
        }

        // 2. Retrieve valid pending messages scheduled for NOW or EARLIER whose appointment is in the FUTURE
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

                // Pacing delay (3 seconds between consecutive dispatches)
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
