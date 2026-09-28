<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model) {
            if (Auth::check() && Auth::user()->barber_shop_id) {
                $model->barber_shop_id = Auth::user()->barber_shop_id;
            }
        });
    }

    public function barberShop()
    {
        return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id');
    }
}
