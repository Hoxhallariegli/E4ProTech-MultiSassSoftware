<?php

namespace App\Services;

use App\Events\FirebaseNotificationRequested;
use App\Models\RealtimeEvent;
use App\Models\EventSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class NotificationRouter
{
    protected static array $processedEvents = [];

    /**
     * Called from every generated Observer on created/updated/deleted.
     * Checks if Firebase Push & SMS Gateway notifications are enabled
     * for this specific shop/salon in EventSetting.
     */
    public function maybeNotify(string $event, Model $item, string $action): void
    {
        $dedupKey = get_class($item) . ':' . $item->getKey() . ':' . $action;
        if (isset(static::$processedEvents[$dedupKey])) {
            Log::info("⏭️ [NotificationRouter] Skipped duplicate trigger for {$dedupKey}");
            return;
        }
        static::$processedEvents[$dedupKey] = true;

        $shopId = $item->barber_shop_id ?? auth()->user()?->barber_shop_id;

        $enabled = true; // Default enabled if no override setting exists

        if ($shopId) {
            $realtimeEvent = RealtimeEvent::where('event', $event)->first();
            if ($realtimeEvent) {
                $setting = EventSetting::where('barber_shop_id', $shopId)
                    ->where('realtime_event_id', $realtimeEvent->id)
                    ->first();

                if ($setting) {
                    $enabled = (bool) $setting->firebase_enabled;
                }
            }
        }

        Log::info("📌 [STEP 2] NotificationRouter::maybeNotify checking event [{$event}] for Shop #{$shopId} (Enabled: " . ($enabled ? 'YES' : 'NO') . ")");

        if (!$enabled) {
            Log::info("⚠️ Notification skipped: Event [{$event}] is disabled for shop #{$shopId} in EventSettings.");
            return;
        }

        Log::info("🚀 [STEP 3] Dispatching FirebaseNotificationRequested event for [{$event}] Model #{$item->getKey()}");

        event(new FirebaseNotificationRequested(
            event: $event,
            modelClass: $item::class,
            modelId: $item->getKey(),
            action: $action,
        ));
    }
}
