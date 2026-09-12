<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired by NotificationRouter ONLY when the target view+action has its
 * Firebase switch turned ON (see App\Models\RealtimeEvent).
 *
 * Hook your existing FCM-sending logic as a listener on this event
 * instead of on each model's *Changed event — that's the single
 * place the on/off switch is enforced.
 */
class FirebaseNotificationRequested
{
    use Dispatchable;

    public function __construct(
        public string $event,       // e.g. 'job-cards.created'
        public string $modelClass,
        public int|string $modelId,
        public string $action,      // 'created' | 'updated' | 'deleted'
    ) {}
}
