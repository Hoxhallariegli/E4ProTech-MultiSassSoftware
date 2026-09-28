<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;
    protected $fillable = ['barber_shop_id', 'booking_id', 'amount', 'method', 'status'];
    protected function casts(): array { return [
            'amount' => 'decimal:2',
        ]; }
    public static function rules($id = null): array { return [
            'barber_shop_id' => ['required', 'integer'],
            'booking_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric'],
            'method' => ['required', \Illuminate\Validation\Rule::in(['cash', 'card'])],
            'status' => ['required', \Illuminate\Validation\Rule::in(['pending', 'paid', 'refunded', 'failed'])],
        ]; }
    public static function sortable(): array { return ['id', 'booking_id', 'amount', 'method', 'status']; }

    protected static function booted(): void
    {
        static::observe(\App\Observers\PaymentObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id'); }

    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\Booking::class, 'booking_id'); }

}