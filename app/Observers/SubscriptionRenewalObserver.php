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

        // Parse duration months from notes or calculate from plan
        $months = 1;
        if (!empty($item->notes) && preg_match('/(\d+)\s*(months|muaj)/i', (string) $item->notes, $matches)) {
            $months = max(1, (int) $matches[1]);
        } elseif ($item->plan && $item->plan->duration_months > 0) {
            $months = (int) $item->plan->duration_months;
        }

        // Exact Accumulation Logic:
        // If the shop currently has remaining active days (expires_at in the future),
        // the new duration is added ON TOP of the existing expiration date!
        // Example: 2 days remaining + 6 months = 6 months and 2 days from now.
        // If expired or null, it starts from today (now()).
        $baseDate = ($shop->expires_at && $shop->expires_at->isFuture()) ? $shop->expires_at->copy() : now();
        $newExpiresAt = $baseDate->copy()->addMonths($months);

        $shop->updateQuietly([
            'expires_at' => $newExpiresAt,
            'active' => true,
        ]);

        Subscription::updateOrCreate(
            ['barber_shop_id' => $shop->id, 'status' => 'active'],
            [
                'plan_id' => $item->plan_id,
                'starts_at' => now(),
                'ends_at' => $newExpiresAt,
                'status' => 'active',
                'auto_renew' => true,
            ]
        );

        \Illuminate\Support\Facades\Log::info("✅ [SubscriptionRenewal] Shop #{$shop->id} ('{$shop->name}') extended by {$months} months to {$newExpiresAt->format('Y-m-d H:i')}");
    }
}
