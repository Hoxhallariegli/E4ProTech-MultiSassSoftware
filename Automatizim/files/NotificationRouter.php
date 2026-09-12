<?php

namespace App\Services;

use App\Events\FirebaseNotificationRequested;
use App\Models\RealtimeEvent;
use Illuminate\Database\Eloquent\Model;

class NotificationRouter
{
    /**
     * Called from every generated Observer on created/updated/deleted.
     * Reverb (realtime UI) already fired separately via {Model}Changed —
     * this only decides whether Firebase/FCM should also fire for this
     * specific view+action, e.g. 'job-cards.created'.
     */
    public function maybeNotify(string $event, Model $item, string $action): void
    {
        if (! RealtimeEvent::firebaseEnabled($event)) {
            return;
        }

        event(new FirebaseNotificationRequested(
            event: $event,
            modelClass: $item::class,
            modelId: $item->getKey(),
            action: $action,
        ));
    }
}
