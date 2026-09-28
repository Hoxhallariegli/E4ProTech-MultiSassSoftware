<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationChannel extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;
    protected $fillable = ['barber_shop_id', 'channel', 'enabled', 'daily_limit'];
    protected function casts(): array { return [
            'enabled' => 'boolean',
            'daily_limit' => 'integer',
        ]; }
    public static function rules($id = null): array { return [
            'barber_shop_id' => ['required', 'integer'],
            'channel' => ['required', \Illuminate\Validation\Rule::in(['sms', 'whatsapp'])],
            'enabled' => ['boolean'],
            'daily_limit' => ['nullable', 'integer'],
        ]; }
    public static function sortable(): array { return ['id', 'channel', 'enabled', 'daily_limit']; }

    protected static function booted(): void
    {
        static::observe(\App\Observers\NotificationChannelObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id'); }

}