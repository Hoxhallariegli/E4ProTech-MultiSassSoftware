<?php

namespace App\Domain\Report\Queries;

use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;

class ReportListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = Report::query()->with(['barber', 'barberShop']);
        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%');
                $query->orWhere('notes', 'like', '%' . $params['search'] . '%');
            });
        }
        if (isset($params['barber_id']) && $params['barber_id']) $query->where('barber_id', $params['barber_id']);
        if (isset($params['barber_shop_id']) && $params['barber_shop_id']) $query->where('barber_shop_id', $params['barber_shop_id']);
        $sortField = in_array($sortField, Report::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}