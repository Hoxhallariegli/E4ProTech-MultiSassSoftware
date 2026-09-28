<?php

namespace App\Models\Traits;

use App\Models\BarberShop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToBarberShop
{
    public static function bootBelongsToBarberShop(): void
    {
        static::creating(function ($model) {
            if (!($model instanceof \App\Models\BarberShop) && !$model->barber_shop_id && Auth::check()) {
                $model->barber_shop_id = Auth::user()->barber_shop_id;
            }
        });

        static::saving(function ($model) {
            foreach ($model->getAttributes() as $key => $value) {
                if ($value === '' || $value === null) {
                    if ($model->hasCast($key, ['int', 'integer', 'decimal', 'float', 'double'])) {
                        $model->setAttribute($key, null);
                    } elseif ($model->hasCast($key, ['bool', 'boolean'])) {
                        $model->setAttribute($key, 0);
                    }
                }
            }
        });

        // Global Scope: Filtrimi automatik i Tenancy
        static::addGlobalScope('barber_shop_access', function (Builder $builder) {
            // Bypass during console commands or if not logged in
            if (app()->runningInConsole() || !Auth::check()) {
                return;
            }

            $user = Auth::user();
            $model = $builder->getModel();

            // 1. NEVER filter Roles or Permissions models globally.
            // Spatie handles team isolation via its own internal logic and the pivot table.
            if ($model instanceof \Spatie\Permission\Models\Role ||
                $model instanceof \Spatie\Permission\Models\Permission) {
                return;
            }

            $tableName = $model->getTable();

            // 2. Super-Admin (Team 0) has total bypass
            $isGlobalAdmin = \Illuminate\Support\Facades\DB::table('model_has_roles')
                ->where('model_id', $user->id)
                ->where('barber_shop_id', 0)
                ->exists();

            if ($isGlobalAdmin) {
                return;
            }

            // 3. Strict Protection: If no active shop, restrict access
            if (!$user->barber_shop_id && !($model instanceof \App\Models\BarberShop)) {
                $builder->whereRaw('1 = 0');
                return;
            }

            // 4. Model-specific isolation logic
            if ($model instanceof \App\Models\BarberShop) {
                // Owners see ALL shops they own or manage
                $builder->where(function ($q) use ($user, $tableName) {
                    $q->where($tableName . '.owner_id', $user->id)
                      ->orWhereHas('users', fn($qu) => $qu->where('users.id', $user->id));
                });
            }
            elseif ($model instanceof \App\Models\User) {
                // Prevent circular logic when loading auth user
                if ($user->id !== null) {
                    $builder->whereIn($tableName . '.id', function($q) use ($user) {
                        $q->select('user_id')
                          ->from('barber_shop_user')
                          ->where('barber_shop_id', $user->barber_shop_id);
                    })->orWhere($tableName . '.id', $user->id);
                }
            }
            else {
                // For all other resources (Barbers, Bookings, etc.)
                $builder->where($tableName . '.barber_shop_id', $user->barber_shop_id);
            }
        });
    }

    public function barberShop()
    {
        return $this->belongsTo(BarberShop::class, 'barber_shop_id');
    }
}
