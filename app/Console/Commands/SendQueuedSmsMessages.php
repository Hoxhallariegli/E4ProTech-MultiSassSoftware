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
        $tz = config('app.timezone', 'Europe/Tirane');
        $now = Carbon::now($tz);

        // Reset stale 'processing' rows older than 1 minute back to 'pending'
        MessageQueue::where('status', 'processing')
            ->where('updated_at', '<', now()->subMinute())
            ->update(['status' => 'pending']);

        // 1. Delete past appointment queue items whose appointment time has already passed
        $pastAppointmentQueues = MessageQueue::whereIn('status', ['pending', 'queued', 'Në Pritje', 'processing'])
            ->whereHas('booking', function($bQuery) use ($now, $tz) {
                $bQuery->whereRaw("appointment_at < ?", [$now->format('Y-m-d H:i:s')]);
            })
            ->get();

        foreach ($pastAppointmentQueues as $past) {
            $past->delete();
            Log::info("🧹 [SendQueuedSmsMessages] Deleted past appointment Queue ID #{$past->id}");
        }

        // 2. Retrieve ALL pending & processing messages scheduled for NOW or EARLIER
        $pendingMessages = MessageQueue::whereIn('status', ['pending', 'queued', 'Në Pritje', 'processing'])
            ->whereRaw("scheduled_at <= ?", [$now->format('Y-m-d H:i:s')])
            ->orderBy('scheduled_at', 'asc')
            ->get();

        if ($pendingMessages->isEmpty()) {
            $this->info("No pending messages to send at {$now->format('Y-m-d H:i:s')} ({$tz}).");
            return;
        }

        $this->info("Found {$pendingMessages->count()} valid pending messages at {$now->format('Y-m-d H:i:s')} ({$tz}).");

        foreach ($pendingMessages as $index => $msg) {
            try {
                // Mark as processing to prevent re-dispatching in concurrent cron runs
                $msg->update(['status' => 'processing']);

                SendSmsToGatewayJob::dispatch($msg->id);
                $this->line("Dispatched Job for Queue ID: {$msg->id} (Channel: {$msg->channel})");

                if ($index < $pendingMessages->count() - 1) {
                    sleep(2);
                }
            } catch (\Exception $e) {
                $msg->update(['status' => 'failed']);
                Log::error("Failed to dispatch message job {$msg->id}: " . $e->getMessage());
            }
        }

        $this->info("Done dispatching.");
    }
}
