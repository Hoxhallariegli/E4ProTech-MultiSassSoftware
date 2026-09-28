<?php

namespace App\Domain\MessageLog\Queries;

use App\Models\MessageLog;
use Illuminate\Database\Eloquent\Builder;

class MessageLogListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = MessageLog::query()->with(['barberShop', 'customer']);
        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%');
                $query->orWhere('message', 'like', '%' . $params['search'] . '%');
            });
        }
        if (isset($params['barber_shop_id']) && $params['barber_shop_id']) $query->where('barber_shop_id', $params['barber_shop_id']);
        if (isset($params['customer_id']) && $params['customer_id']) $query->where('customer_id', $params['customer_id']);
        $sortField = in_array($sortField, MessageLog::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}