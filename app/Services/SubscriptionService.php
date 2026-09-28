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
