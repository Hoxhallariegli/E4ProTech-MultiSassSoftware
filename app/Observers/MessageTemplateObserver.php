<?php

namespace App\Observers;

use App\Events\MessageTemplateChanged;
use App\Models\MessageTemplate;
use App\Services\NotificationRouter;

class MessageTemplateObserver
{
    public function created(MessageTemplate $item): void
    {
        try {
            event(new MessageTemplateChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('message-templates.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for MessageTemplate: " . $e->getMessage());
        }
    }

    public function updated(MessageTemplate $item): void
    {
        try {
            event(new MessageTemplateChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('message-templates.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for MessageTemplate: " . $e->getMessage());
        }
    }

    public function deleted(MessageTemplate $item): void
    {
        try {
            event(new MessageTemplateChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('message-templates.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for MessageTemplate: " . $e->getMessage());
        }
    }
}
