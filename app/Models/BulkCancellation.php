<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkCancellation extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;
    protected $fillable = ['barber_shop_id', 'barber_id', 'start_at', 'end_at', 'reason', 'affected_count'];
    protected function casts(): array { return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'affected_count' => 'integer',
        ]; }
    public static function rules($id = null): array { return [
            'barber_shop_id' => ['required', 'integer'],
            'barber_id' => ['required', 'integer'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'affected_count' => ['nullable', 'integer'],
        ]; }
    public static function sortable(): array { return ['id', 'barber_id', 'start_at', 'end_at', 'reason', 'affected_count']; }

    protected static function booted(): void
    {
        static::observe(\App\Observers\BulkCancellationObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id'); }

    public function barber(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\Barber::class, 'barber_id'); }

}