<?php

namespace App\Observers;

use App\Events\BarberShopChanged;
use App\Models\BarberShop;
use App\Services\NotificationRouter;

class BarberShopObserver
{
    public function created(BarberShop $item): void
    {
        try {
            event(new BarberShopChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('barber-shops.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for BarberShop: " . $e->getMessage());
        }
    }

    public function updated(BarberShop $item): void
    {
        try {
            if ($item->isDirty('expires_at') && $item->expires_at) {
                $item->subscriptions()->where('status', 'active')->update([
                    'ends_at' => $item->expires_at
                ]);
            }
            event(new BarberShopChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('barber-shops.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for BarberShop: " . $e->getMessage());
        }
    }

    public function deleted(BarberShop $item): void
    {
        try {
            event(new BarberShopChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('barber-shops.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for BarberShop: " . $e->getMessage());
        }
    }
}
