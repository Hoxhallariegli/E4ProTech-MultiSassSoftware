<?php

namespace App\Observers;

use App\Events\ShopFrontPageSettingChanged;
use App\Models\ShopFrontPageSetting;
use App\Services\NotificationRouter;

class ShopFrontPageSettingObserver
{
    public function created(ShopFrontPageSetting $item): void
    {
        try {
            event(new ShopFrontPageSettingChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('shop-front-page-settings.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for ShopFrontPageSetting: " . $e->getMessage());
        }
    }

    public function updated(ShopFrontPageSetting $item): void
    {
        try {
            event(new ShopFrontPageSettingChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('shop-front-page-settings.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for ShopFrontPageSetting: " . $e->getMessage());
        }
    }

    public function deleted(ShopFrontPageSetting $item): void
    {
        try {
            event(new ShopFrontPageSettingChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('shop-front-page-settings.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for ShopFrontPageSetting: " . $e->getMessage());
        }
    }
}
