<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MessageQueue;
use App\Jobs\SendSmsToGatewayJob;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendQueuedSmsMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:send-queued';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch pending SMS from queue to Gateway devices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // Retrieve pending messages scheduled for now or earlier
        $pendingMessages = MessageQueue::where('status', 'pending')
            ->where('channel', 'sms')
            ->where('scheduled_at', '<=', $now)
            ->get();

        if ($pendingMessages->isEmpty()) {
            $this->info("No pending SMS messages to send at {$now}.");
            return;
        }

        $this->info("Found {$pendingMessages->count()} pending SMS messages.");

        foreach ($pendingMessages as $msg) {
            // Process the message directly or via Job (SendSmsToGatewayJob handles FCM trigger)
            try {
                SendSmsToGatewayJob::dispatch($msg->id);
                $this->line("Dispatched Job for SMS Queue ID: {$msg->id}");
            } catch (\Exception $e) {
                Log::error("Failed to dispatch SMS job {$msg->id}: " . $e->getMessage());
            }
        }

        $this->info("Done dispatching.");
    }
}
