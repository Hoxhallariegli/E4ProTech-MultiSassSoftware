<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;
    protected $fillable = ['barber_shop_id', 'plan_id', 'starts_at', 'ends_at', 'status', 'auto_renew'];
    protected function casts(): array { return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'auto_renew' => 'boolean',
        ]; }
    public static function rules($id = null): array { return [
            'barber_shop_id' => ['required', 'integer'],
            'plan_id' => ['required', 'integer'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date'],
            'status' => ['required', \Illuminate\Validation\Rule::in(['trial', 'active', 'expired', 'cancelled'])],
            'auto_renew' => ['boolean'],
        ]; }
    public static function sortable(): array { return ['id', 'plan_id', 'starts_at', 'ends_at', 'status', 'auto_renew']; }

    protected static function booted(): void
    {
        static::observe(\App\Observers\SubscriptionObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id'); }

    public function plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\Plan::class, 'plan_id'); }

}