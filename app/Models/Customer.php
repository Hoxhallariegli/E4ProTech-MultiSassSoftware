<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;
    protected $fillable = ['barber_shop_id', 'name', 'phone', 'email', 'photo', 'total_bookings', 'no_show_count', 'blocked_at'];
    protected function casts(): array { return [
            'total_bookings' => 'integer',
            'no_show_count' => 'integer',
            'blocked_at' => 'datetime',
        ]; }
    public static function rules($id = null): array { return [
            'barber_shop_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'string', 'max:255'],
            'total_bookings' => ['nullable', 'integer'],
            'no_show_count' => ['nullable', 'integer'],
            'blocked_at' => ['nullable', 'date'],
        ]; }
    public static function sortable(): array { return ['id', 'name', 'phone', 'email', 'photo', 'total_bookings', 'no_show_count', 'blocked_at']; }

    protected static function booted(): void
    {
        static::observe(\App\Observers\CustomerObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id'); }

    public static function recalculateStats(?int $customerId): void
    {
        if (!$customerId) return;

        $totalBookings = Booking::where('customer_id', $customerId)
            ->where('status', '!=', 'cancelled')
            ->count();

        $noShowCount = Booking::where('customer_id', $customerId)
            ->where('status', 'no-show')
            ->count();

        static::where('id', $customerId)->update([
            'total_bookings' => $totalBookings,
            'no_show_count' => $noShowCount,
        ]);
    }

}
