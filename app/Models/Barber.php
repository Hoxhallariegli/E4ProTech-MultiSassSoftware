<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;

    protected $fillable = ['barber_shop_id', 'user_id', 'name', 'phone', 'photo', 'bio', 'active'];

    protected function casts(): array {
        return [
            'active' => 'boolean',
        ];
    }

    public static function rules($id = null): array {
        return [
            'barber_shop_id' => ['required', 'integer', 'exists:barber_shops,id'],
            'user_id' => ['required', 'string', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'active' => ['boolean'],
        ];
    }

    public static function sortable(): array {
        return ['id', 'user_id', 'name', 'phone', 'photo', 'bio', 'active'];
    }

    protected static function booted(): void
    {
        static::observe(\App\Observers\BarberObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
