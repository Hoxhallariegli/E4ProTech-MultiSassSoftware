<?php

namespace App\Observers;

use App\Events\SubscriptionChanged;
use App\Models\Subscription;
use App\Services\NotificationRouter;

class SubscriptionObserver
{
    public function created(Subscription $item): void
    {
        try {
            event(new SubscriptionChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('subscriptions.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for Subscription: " . $e->getMessage());
        }
    }

    public function updated(Subscription $item): void
    {
        try {
            if ($item->isDirty('ends_at') && $item->ends_at && $item->barberShop) {
                $item->barberShop->updateQuietly([
                    'expires_at' => $item->ends_at
                ]);
            }
            event(new SubscriptionChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('subscriptions.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for Subscription: " . $e->getMessage());
        }
    }

    public function deleted(Subscription $item): void
    {
        try {
            event(new SubscriptionChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('subscriptions.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for Subscription: " . $e->getMessage());
        }
    }
}
