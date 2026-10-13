<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;
    protected $fillable = ['period_type', 'report_date', 'total_bookings', 'total_revenue', 'total_completed', 'total_cancelled', 'notes', 'barber_id', 'barber_shop_id'];
    protected function casts(): array { return [
            'report_date' => 'datetime',
            'total_bookings' => 'integer',
            'total_revenue' => 'decimal:2',
            'total_completed' => 'integer',
            'total_cancelled' => 'integer',
        ]; }
    public static function rules($id = null): array { return [
            'period_type' => ['required', \Illuminate\Validation\Rule::in(['daily', 'weekly', 'monthly', 'custom'])],
            'report_date' => ['required', 'date'],
            'total_bookings' => ['required', 'integer'],
            'total_revenue' => ['required', 'numeric'],
            'total_completed' => ['required', 'integer'],
            'total_cancelled' => ['required', 'integer'],
            'notes' => ['nullable', 'string'],
            'barber_id' => ['nullable', 'integer'],
            'barber_shop_id' => ['required', 'integer'],
        ]; }
    public static function sortable(): array { return ['id', 'period_type', 'report_date', 'total_bookings', 'total_revenue', 'total_completed', 'total_cancelled', 'notes', 'barber_id']; }

    protected static function booted(): void
    {
        static::observe(\App\Observers\ReportObserver::class);
    }

    public function barber(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\Barber::class, 'barber_id'); }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id'); }

}