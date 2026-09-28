<?php

namespace App\Domain\Booking\Queries;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;

class BookingListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = Booking::query()->with(['barberShop', 'barber', 'service', 'customer']);
        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%');
                $query->orWhere('notes', 'like', '%' . $params['search'] . '%');
            });
        }
        if (isset($params['barber_shop_id']) && $params['barber_shop_id']) $query->where('barber_shop_id', $params['barber_shop_id']);
        if (isset($params['barber_id']) && $params['barber_id']) $query->where('barber_id', $params['barber_id']);
        if (isset($params['service_id']) && $params['service_id']) $query->where('service_id', $params['service_id']);
        if (isset($params['customer_id']) && $params['customer_id']) $query->where('customer_id', $params['customer_id']);
        $sortField = in_array($sortField, Booking::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}