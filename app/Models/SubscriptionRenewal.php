<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionRenewal extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;

    protected $fillable = [
        'barber_shop_id',
        'plan_id',
        'payment_method',
        'transfer_document',
        'amount',
        'notes',
        'status',
    ];

    protected function casts(): array {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public static function rules($id = null): array {
        return [
            'barber_shop_id' => ['required', 'integer', 'exists:barber_shops,id'],
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'payment_method' => ['required', \Illuminate\Validation\Rule::in(['bank_transfer', 'cash', 'card', 'online'])],
            'transfer_document' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', \Illuminate\Validation\Rule::in(['pending', 'approved', 'rejected'])],
        ];
    }

    public static function sortable(): array {
        return ['id', 'barber_shop_id', 'plan_id', 'payment_method', 'transfer_document', 'amount', 'notes', 'status'];
    }

    protected static function booted(): void
    {
        static::observe(\App\Observers\SubscriptionRenewalObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id');
    }

    public function plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\Plan::class, 'plan_id');
    }
}
