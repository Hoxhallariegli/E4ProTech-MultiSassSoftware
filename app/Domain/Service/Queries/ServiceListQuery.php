<?php

namespace App\Domain\Service\Queries;

use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;

class ServiceListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = Service::query()->with(['barberShop']);
        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%');
                $query->orWhere('name', 'like', '%' . $params['search'] . '%');
                $query->orWhere('description', 'like', '%' . $params['search'] . '%');
                $query->orWhere('category', 'like', '%' . $params['search'] . '%');
            });
        }
        if (isset($params['barber_shop_id']) && $params['barber_shop_id']) $query->where('barber_shop_id', $params['barber_shop_id']);
        $sortField = in_array($sortField, Service::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}