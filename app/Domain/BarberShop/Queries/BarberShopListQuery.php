<?php

namespace App\Domain\BarberShop\Queries;

use App\Models\BarberShop;
use Illuminate\Database\Eloquent\Builder;

class BarberShopListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = BarberShop::query()->with(['owner']);
        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%');
                $query->orWhere('name', 'like', '%' . $params['search'] . '%');
                $query->orWhere('app_name', 'like', '%' . $params['search'] . '%');
                $query->orWhere('slug', 'like', '%' . $params['search'] . '%');
                $query->orWhere('primary_color', 'like', '%' . $params['search'] . '%');
                $query->orWhere('secondary_color', 'like', '%' . $params['search'] . '%');
                $query->orWhere('timezone', 'like', '%' . $params['search'] . '%');
            });
        }
        if (isset($params['owner_id']) && $params['owner_id']) $query->where('owner_id', $params['owner_id']);
        $sortField = in_array($sortField, BarberShop::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}