<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;
    protected $fillable = ['barber_shop_id', 'barber_id', 'customer_id', 'booking_id', 'rating', 'comment'];
    protected function casts(): array { return [
            'rating' => 'integer',
        ]; }
    public static function rules($id = null): array { return [
            'barber_shop_id' => ['required', 'integer'],
            'barber_id' => ['required', 'integer'],
            'customer_id' => ['required', 'integer'],
            'booking_id' => ['required', 'integer'],
            'rating' => ['required', 'integer'],
            'comment' => ['nullable', 'string'],
        ]; }
    public static function sortable(): array { return ['id', 'barber_id', 'customer_id', 'booking_id', 'rating', 'comment']; }

    protected static function booted(): void
    {
        static::observe(\App\Observers\ReviewObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id'); }

    public function barber(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\Barber::class, 'barber_id'); }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\Customer::class, 'customer_id'); }

    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\Booking::class, 'booking_id'); }

}