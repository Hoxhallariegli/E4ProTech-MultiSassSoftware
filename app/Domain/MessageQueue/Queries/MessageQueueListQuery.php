<?php

namespace App\Domain\MessageQueue\Queries;

use App\Models\MessageQueue;
use Illuminate\Database\Eloquent\Builder;

class MessageQueueListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = MessageQueue::query()->with(['barberShop', 'booking.customer']);
        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%');
                $query->orWhere('phone_number', 'like', '%' . $params['search'] . '%');
                $query->orWhere('message_content', 'like', '%' . $params['search'] . '%');
            });
        }
        if (isset($params['barber_shop_id']) && $params['barber_shop_id']) $query->where('barber_shop_id', $params['barber_shop_id']);
        if (isset($params['booking_id']) && $params['booking_id']) $query->where('booking_id', $params['booking_id']);
        $sortField = in_array($sortField, MessageQueue::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}