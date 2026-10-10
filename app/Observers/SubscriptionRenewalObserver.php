<?php

namespace App\Observers;

use App\Events\SubscriptionRenewalChanged;
use App\Models\SubscriptionRenewal;
use App\Models\Subscription;
use App\Services\NotificationRouter;

class SubscriptionRenewalObserver
{
    public function created(SubscriptionRenewal $item): void
    {
        try {
            event(new SubscriptionRenewalChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('subscription-renewals.created', $item, 'created');

            if ($item->status === 'approved') {
                $this->processApproval($item);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for SubscriptionRenewal: " . $e->getMessage());
        }
    }

    public function updated(SubscriptionRenewal $item): void
    {
        try {
            event(new SubscriptionRenewalChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('subscription-renewals.updated', $item, 'updated');

            if ($item->isDirty('status') && $item->status === 'approved') {
                $this->processApproval($item);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for SubscriptionRenewal: " . $e->getMessage());
        }
    }

    public function deleted(SubscriptionRenewal $item): void
    {
        try {
            event(new SubscriptionRenewalChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('subscription-renewals.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for SubscriptionRenewal: " . $e->getMessage());
        }
    }

    protected function processApproval(SubscriptionRenewal $item): void
    {
        $shop = $item->barberShop;
        if (!$shop) return;

        $plan = $item->plan;
        $daysToAdd = ($plan && strtolower((string) ($plan->billing_cycle ?? '')) === 'yearly') ? 365 : 30;

        $baseDate = ($shop->expires_at && $shop->expires_at->isFuture()) ? $shop->expires_at : now();
        $newExpiresAt = $baseDate->copy()->addDays($daysToAdd);

        $shop->update(['expires_at' => $newExpiresAt]);

        Subscription::create([
            'barber_shop_id' => $shop->id,
            'plan_id' => $item->plan_id,
            'starts_at' => now(),
            'ends_at' => $newExpiresAt,
            'status' => 'active',
            'amount' => $item->amount,
        ]);

        \Illuminate\Support\Facades\Log::info("✅ [SubscriptionRenewal] Shop #{$shop->id} ('{$shop->name}') subscription extended to {$newExpiresAt->format('Y-m-d H:i')}");
    }
}
