<?php

namespace App\Domain\WorkingHour\Queries;

use App\Models\WorkingHour;
use Illuminate\Database\Eloquent\Builder;

class WorkingHourListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = WorkingHour::query()->with(['barber']);
        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%');

            });
        }
        if (isset($params['barber_id']) && $params['barber_id']) $query->where('barber_id', $params['barber_id']);
        $sortField = in_array($sortField, WorkingHour::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}