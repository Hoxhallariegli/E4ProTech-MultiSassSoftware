<?php

namespace App\Domain\Subscription\Queries;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = Subscription::query()->with(['barberShop', 'plan']);
        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%');

            });
        }
        if (isset($params['barber_shop_id']) && $params['barber_shop_id']) $query->where('barber_shop_id', $params['barber_shop_id']);
        if (isset($params['plan_id']) && $params['plan_id']) $query->where('plan_id', $params['plan_id']);
        $sortField = in_array($sortField, Subscription::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}