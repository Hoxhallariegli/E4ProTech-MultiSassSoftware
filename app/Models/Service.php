<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;

    protected $fillable = [
        'barber_shop_id',
        'name',
        'description',
        'price',
        'price_max',
        'duration_minutes',
        'category',
        'active',
    ];

    protected function casts(): array {
        return [
            'price' => 'decimal:2',
            'price_max' => 'decimal:2',
            'duration_minutes' => 'integer',
            'active' => 'boolean',
        ];
    }

    public static function rules($id = null): array {
        return [
            'barber_shop_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'gte:price'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'category' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
        ];
    }

    public static function sortable(): array {
        return ['id', 'name', 'description', 'price', 'price_max', 'duration_minutes', 'category', 'active'];
    }

    protected static function booted(): void
    {
        static::observe(\App\Observers\ServiceObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id');
    }

    public function getFormattedPriceRangeAttribute(): string
    {
        $shop = $this->relationLoaded('barberShop') ? $this->barberShop : null;
        $currency = $shop?->resolved_currency ?: 'Lekë';

        $min = number_format((float) $this->price, 0);
        if ($this->price_max && (float) $this->price_max > (float) $this->price) {
            $max = number_format((float) $this->price_max, 0);
            return "{$min} - {$max} {$currency}";
        }

        return "{$min} {$currency}";
    }
}
