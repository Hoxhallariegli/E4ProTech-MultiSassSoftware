<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;

    protected $fillable = [
        'barber_shop_id',
        'barber_id',
        'service_id',
        'customer_id',
        'appointment_at',
        'status',
        'payment_status',
        'total_price',
        'notes',
        'source',
    ];

    protected function casts(): array {
        return [
            'appointment_at' => 'datetime',
            'total_price' => 'decimal:2',
        ];
    }

    public static function rules($id = null): array {
        return [
            'barber_shop_id' => ['required', 'integer'],
            'barber_id' => ['required', 'integer'],
            'service_id' => ['required', 'integer'],
            'customer_id' => ['required', 'integer'],
            'appointment_at' => ['required', 'date'],
            'status' => ['required', \Illuminate\Validation\Rule::in(['pending', 'confirmed', 'completed', 'cancelled', 'no-show'])],
            'total_price' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
            'source' => ['required', \Illuminate\Validation\Rule::in(['online', 'walk-in', 'phone'])],
        ];
    }

    public static function sortable(): array {
        return ['id', 'barber_id', 'service_id', 'customer_id', 'appointment_at', 'status', 'total_price', 'notes', 'source'];
    }

    protected static function booted(): void
    {
        static::observe(\App\Observers\BookingObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id');
    }

    public function barber(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\Barber::class, 'barber_id');
    }

    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\Service::class, 'service_id');
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }

    public static function checkOverlap(?int $barberId, $appointmentAt, ?int $serviceId, $ignoreId = null, bool $allowLunchOverride = true): void
    {
        if (!$barberId || !$appointmentAt) {
            return;
        }

        $start = \Carbon\Carbon::parse($appointmentAt);

        $durationMinutes = 30;
        if ($serviceId) {
            $service = \App\Models\Service::find($serviceId);
            if ($service && $service->duration_minutes > 0) {
                $durationMinutes = (int) $service->duration_minutes;
            }
        }

        $end = (clone $start)->addMinutes($durationMinutes);

        // Check overlap ONLY with existing active bookings for the same staff member
        $query = static::query()
            ->where('barber_id', $barberId)
            ->where('status', '!=', 'cancelled')
            ->whereDate('appointment_at', $start->format('Y-m-d'));

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $existingBookings = $query->with('service')->get();

        foreach ($existingBookings as $existing) {
            $eStart = $existing->appointment_at;
            $eDuration = $existing->service?->duration_minutes ?? 30;
            $eEnd = (clone $eStart)->addMinutes($eDuration);

            if ($eStart < $end && $eEnd > $start) {
                $shop = auth()->check() ? auth()->user()?->barberShop : null;
                $staffLabel = $shop ? $shop->resolved_staff_label : 'Stafi';
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'appointment_at' => [
                        "{$staffLabel} është i zënë me një takim tjetër në këtë orar (" . $eStart->format('H:i') . " - " . $eEnd->format('H:i') . ")."
                    ]
                ]);
            }
        }
    }
}
