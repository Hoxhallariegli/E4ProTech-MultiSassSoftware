<?php

namespace App\Observers;

use App\Events\ReviewChanged;
use App\Models\Review;
use App\Services\NotificationRouter;

class ReviewObserver
{
    public function created(Review $item): void
    {
        try {
            event(new ReviewChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('reviews.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for Review: " . $e->getMessage());
        }
    }

    public function updated(Review $item): void
    {
        try {
            event(new ReviewChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('reviews.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for Review: " . $e->getMessage());
        }
    }

    public function deleted(Review $item): void
    {
        try {
            event(new ReviewChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('reviews.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for Review: " . $e->getMessage());
        }
    }
}
