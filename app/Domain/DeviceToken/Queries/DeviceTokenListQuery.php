<?php

namespace App\Domain\DeviceToken\Queries;

use App\Models\DeviceToken;
use Illuminate\Database\Eloquent\Builder;

class DeviceTokenListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = DeviceToken::query()->with(['barberShop', 'user']);
        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%');
                $query->orWhere('fcm_token', 'like', '%' . $params['search'] . '%');
            });
        }
        if (isset($params['barber_shop_id']) && $params['barber_shop_id']) $query->where('barber_shop_id', $params['barber_shop_id']);
        if (isset($params['user_id']) && $params['user_id']) $query->where('user_id', $params['user_id']);
        $sortField = in_array($sortField, DeviceToken::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}