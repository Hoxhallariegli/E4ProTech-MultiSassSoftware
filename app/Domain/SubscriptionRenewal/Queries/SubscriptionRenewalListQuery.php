<?php

namespace App\Domain\SubscriptionRenewal\Queries;

use App\Models\SubscriptionRenewal;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionRenewalListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = SubscriptionRenewal::query()->with(['barberShop', 'plan']);

        if (auth()->check() && !auth()->user()->is_global_admin) {
            $query->where('barber_shop_id', auth()->user()->barber_shop_id);
        }

        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%')
                      ->orWhere('notes', 'like', '%' . $params['search'] . '%')
                      ->orWhere('payment_method', 'like', '%' . $params['search'] . '%');
            });
        }

        if (isset($params['plan_id']) && $params['plan_id']) {
            $query->where('plan_id', $params['plan_id']);
        }

        $sortField = in_array($sortField, SubscriptionRenewal::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}
