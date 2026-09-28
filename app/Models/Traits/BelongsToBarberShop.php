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
            if (!$model->barber_shop_id && Auth::check()) {
                // Caktojmë automatikisht dyqanin aktiv të përdoruesit
                $model->barber_shop_id = Auth::user()->barber_shop_id;
            }
        });

        // Global Scope: Filtrimi automatik
        static::addGlobalScope('barber_shop_access', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();

                // Nëse është Admin, mos apliko filtrim
                if ($user->hasRole('admin')) {
                    return;
                }

                // Përndryshe, shih vetëm të dhënat e dyqanit aktiv
                $builder->where('barber_shop_id', $user->barber_shop_id);
            }
        });
    }

    public function barberShop()
    {
        return $this->belongsTo(BarberShop::class, 'barber_shop_id');
    }
}
