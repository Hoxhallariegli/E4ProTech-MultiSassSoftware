<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;

    protected $fillable = [
        'barber_shop_id',
        'user_id',
        'fcm_token',
        'platform',
        'is_sms_gateway',
        'device_name',
        'last_used_at'
    ];

    protected function casts(): array {
        return [
            'last_used_at' => 'datetime',
            'is_sms_gateway' => 'boolean',
        ];
    }

    public static function rules($id = null): array {
        return [
            'barber_shop_id' => ['required', 'integer'],
            'user_id' => ['required', 'string'],
            'fcm_token' => ['required', 'string', 'max:255'],
            'platform' => ['required', \Illuminate\Validation\Rule::in(['android', 'ios', 'web'])],
            'is_sms_gateway' => ['boolean'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'last_used_at' => ['nullable', 'date'],
        ];
    }

    public static function sortable(): array {
        return ['id', 'user_id', 'fcm_token', 'platform', 'is_sms_gateway', 'device_name', 'last_used_at'];
    }

    protected static function booted(): void
    {
        static::observe(\App\Observers\DeviceTokenObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
