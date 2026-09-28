<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;
    protected $fillable = ['barber_shop_id', 'name', 'description', 'price', 'duration_minutes', 'category', 'active'];
    protected function casts(): array { return [
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'active' => 'boolean',
        ]; }
    public static function rules($id = null): array { return [
            'barber_shop_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric'],
            'duration_minutes' => ['required', 'integer'],
            'category' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
        ]; }
    public static function sortable(): array { return ['id', 'name', 'description', 'price', 'duration_minutes', 'category', 'active']; }

    protected static function booted(): void
    {
        static::observe(\App\Observers\ServiceObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id'); }

}