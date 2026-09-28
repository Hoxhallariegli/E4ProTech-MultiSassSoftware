<?php

namespace App\Observers;

use App\Events\MessageQueueChanged;
use App\Models\MessageQueue;
use App\Services\NotificationRouter;

class MessageQueueObserver
{
    public function created(MessageQueue $item): void
    {
        try {
            event(new MessageQueueChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('message-queues.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for MessageQueue: " . $e->getMessage());
        }
    }

    public function updated(MessageQueue $item): void
    {
        try {
            event(new MessageQueueChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('message-queues.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for MessageQueue: " . $e->getMessage());
        }
    }

    public function deleted(MessageQueue $item): void
    {
        try {
            event(new MessageQueueChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('message-queues.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for MessageQueue: " . $e->getMessage());
        }
    }
}
