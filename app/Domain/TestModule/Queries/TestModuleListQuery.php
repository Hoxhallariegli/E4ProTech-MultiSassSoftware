<?php

namespace App\Domain\TestModule\Queries;

use App\Models\TestModule;
use Illuminate\Database\Eloquent\Builder;

class TestModuleListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = TestModule::query()->with(['user']);
        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%');
                $query->orWhere('name', 'like', '%' . $params['search'] . '%');
                $query->orWhere('description', 'like', '%' . $params['search'] . '%');
            });
        }
        if (isset($params['user_id']) && $params['user_id']) $query->where('user_id', $params['user_id']);
        $sortField = in_array($sortField, TestModule::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}