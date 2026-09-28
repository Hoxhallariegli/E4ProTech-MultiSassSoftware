<?php

namespace App\Services;

use App\Models\BarberShop;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Subscription;

class SubscriptionService
{
    /**
     * Check if a shop can add more barbers based on its active plan.
     */
    public function canAddBarber(BarberShop $shop): bool
    {
        $subscription = $this->getActiveSubscription($shop);
        if (!$subscription) return false;

        $plan = $subscription->plan;
        if (!$plan) return false;

        $currentCount = Barber::where('barber_shop_id', $shop->id)->count();

        return $currentCount < $plan->max_barbers;
    }

    /**
     * Check if a shop can add more services based on its active plan.
     */
    public function canAddService(BarberShop $shop): bool
    {
        $subscription = $this->getActiveSubscription($shop);
        if (!$subscription) return false;

        $plan = $subscription->plan;
        if (!$plan) return false;

        $currentCount = Service::where('barber_shop_id', $shop->id)->count();

        return $currentCount < $plan->max_services;
    }

    /**
     * Check if a user (owner) can add more shops.
     */
    public function canAddShop(\App\Models\User $user): bool
    {
        // Global admin can always add shops
        $isGlobalAdmin = \Illuminate\Support\Facades\DB::table('model_has_roles')
            ->where('model_id', $user->id)
            ->where('barber_shop_id', 0)
            ->exists();

        if ($isGlobalAdmin) return true;

        // Get the highest 'max_shops' from all active subscriptions of this owner
        $maxShops = \App\Models\Subscription::whereHas('barberShop', function($q) use ($user) {
                $q->where('owner_id', $user->id);
            })
            ->whereIn('status', ['active', 'trial'])
            ->where('ends_at', '>', now())
            ->with('plan')
            ->get()
            ->max(fn($s) => $s->plan->max_shops ?? 1) ?? 1;

        $currentShopsCount = \App\Models\BarberShop::where('owner_id', $user->id)->count();

        return $currentShopsCount < $maxShops;
    }

    /**
     * Get the active subscription for a shop.
     */
    public function getActiveSubscription(BarberShop $shop): ?Subscription
    {
        return Subscription::where('barber_shop_id', $shop->id)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->first() ??
            Subscription::where('barber_shop_id', $shop->id)
            ->where('status', 'trial')
            ->where('ends_at', '>', now())
            ->first();
    }
}
