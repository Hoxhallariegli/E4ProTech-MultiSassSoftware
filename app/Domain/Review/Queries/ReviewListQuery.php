<?php

namespace App\Domain\Review\Queries;

use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;

class ReviewListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = Review::query()->with(['barberShop', 'barber', 'customer', 'booking.customer']);
        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%');
                $query->orWhere('comment', 'like', '%' . $params['search'] . '%');
            });
        }
        if (isset($params['barber_shop_id']) && $params['barber_shop_id']) $query->where('barber_shop_id', $params['barber_shop_id']);
        if (isset($params['barber_id']) && $params['barber_id']) $query->where('barber_id', $params['barber_id']);
        if (isset($params['customer_id']) && $params['customer_id']) $query->where('customer_id', $params['customer_id']);
        if (isset($params['booking_id']) && $params['booking_id']) $query->where('booking_id', $params['booking_id']);
        $sortField = in_array($sortField, Review::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}