<?php

namespace App\Services;

use App\Events\FirebaseNotificationRequested;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class NotificationRouter
{
    protected static array $processedEvents = [];

    /**
     * Called from every generated Observer on created/updated/deleted.
     * Dispatches Firebase Push & SMS Gateway notifications directly.
     */
    public function maybeNotify(string $event, Model $item, string $action): void
    {
        $dedupKey = get_class($item) . ':' . $item->getKey() . ':' . $action;
        if (isset(static::$processedEvents[$dedupKey])) {
            Log::info("⏭️ [NotificationRouter] Skipped duplicate trigger for {$dedupKey}");
            return;
        }
        static::$processedEvents[$dedupKey] = true;

        Log::info("🚀 [STEP 3] Dispatching FirebaseNotificationRequested event for [{$event}] Model #{$item->getKey()}");

        event(new FirebaseNotificationRequested(
            event: $event,
            modelClass: $item::class,
            modelId: $item->getKey(),
            action: $action,
        ));
    }
}
