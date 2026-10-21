<?php

namespace App\Domain\BarberShop\Queries;

use App\Models\BarberShop;
use Illuminate\Database\Eloquent\Builder;

class BarberShopListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = BarberShop::query()->with(['owner']);

        if (auth()->check()) {
            $user = auth()->user();
            if (!$user->is_global_admin) {
                $userShopIdsFromRoles = \Illuminate\Support\Facades\DB::table('model_has_roles')
                    ->where('model_id', $user->id)
                    ->where('barber_shop_id', '>', 0)
                    ->pluck('barber_shop_id')
                    ->toArray();

                $query->where(function ($q) use ($user, $userShopIdsFromRoles) {
                    $q->where('owner_id', $user->id);

                    if ($user->barber_shop_id) {
                        $q->orWhere('id', $user->barber_shop_id);
                    }

                    if (!empty($userShopIdsFromRoles)) {
                        $q->orWhereIn('id', $userShopIdsFromRoles);
                    }

                    $q->orWhereHas('users', function ($qu) use ($user) {
                        $qu->where('users.id', $user->id);
                    });
                });
            }
        }

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
