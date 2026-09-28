<?php

namespace App\Domain\EventSetting\Queries;

use App\Models\EventSetting;
use Illuminate\Database\Eloquent\Builder;

class EventSettingListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = EventSetting::query()->with(['barberShop', 'realtimeEvent']);
        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%');

            });
        }
        if (isset($params['barber_shop_id']) && $params['barber_shop_id']) $query->where('barber_shop_id', $params['barber_shop_id']);
        if (isset($params['realtime_event_id']) && $params['realtime_event_id']) $query->where('realtime_event_id', $params['realtime_event_id']);
        $sortField = in_array($sortField, EventSetting::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}