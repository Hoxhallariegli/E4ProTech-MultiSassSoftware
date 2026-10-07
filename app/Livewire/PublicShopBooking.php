<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BarberShop;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\WorkingHour;
use Carbon\Carbon;

class PublicShopBooking extends Component
{
    public BarberShop $shop;

    public $selectedServiceId = null;
    public $selectedBarberId = null;
    public $bookingDate = null;
    public $bookingTime = null;

    public $customerName = '';
    public $customerPhone = '';
    public $notes = '';

    public $bookingSuccess = false;
    public $createdBooking = null;

    public function mount(BarberShop $shop)
    {
        $this->shop = $shop;
        $this->bookingDate = Carbon::now()->format('Y-m-d');

        // Preselect first service if available
        $services = $this->services;
        if ($services->isNotEmpty()) {
            $this->selectedServiceId = $services->first()->id;
        }

        // Preselect first barber if available
        $barbers = $this->barbers;
        if ($barbers->isNotEmpty()) {
            $this->selectedBarberId = $barbers->first()->id;
        }
    }

    public function selectService($serviceId)
    {
        $this->selectedServiceId = $serviceId;
        $this->bookingTime = null;
    }

    public function selectBarber($barberId)
    {
        $this->selectedBarberId = $barberId;
        $this->bookingTime = null;
    }

    public function updatedBookingDate()
    {
        $this->bookingTime = null;
    }

    public function resetForm()
    {
        $this->bookingSuccess = false;
        $this->createdBooking = null;
        $this->customerName = '';
        $this->customerPhone = '';
        $this->notes = '';
        $this->bookingTime = null;
        $this->bookingDate = Carbon::now()->format('Y-m-d');
    }

    public function getServicesProperty()
    {
        return Service::withoutGlobalScope('barber_shop_access')
            ->where('barber_shop_id', $this->shop->id)
            ->where('active', true)
            ->get();
    }

    public function getBarbersProperty()
    {
        return Barber::withoutGlobalScope('barber_shop_access')
            ->where('barber_shop_id', $this->shop->id)
            ->where('active', true)
            ->get();
    }

    public function getAvailableTimeSlotsProperty()
    {
        if (!$this->bookingDate) {
            return [];
        }

        $now = Carbon::now();

        try {
            $date = Carbon::parse($this->bookingDate);
        } catch (\Throwable $e) {
            $date = Carbon::today();
        }
        $dateStr = $date->format('Y-m-d');

        $workingHour = null;

        // Check if barber has an explicit working hours record marked as closed
        if ($this->selectedBarberId) {
            $dayOfWeekLower = strtolower($date->format('l'));
            $workingHour = WorkingHour::where('barber_id', $this->selectedBarberId)
                ->where(function($q) use ($dayOfWeekLower) {
                    $q->whereRaw('LOWER(day_of_week) = ?', [$dayOfWeekLower]);
                })
                ->first();

            // ONLY if explicitly closed, return []
            if ($workingHour && (bool)$workingHour->is_closed === true) {
                return [];
            }
        }

        $normalizeTime = function ($val) {
            if (!$val) return null;
            try {
                return Carbon::parse((string) $val)->format('H:i');
            } catch (\Throwable $e) {
                return null;
            }
        };

        $openTimeStr = ($workingHour && !empty($workingHour->open_time)) ? $normalizeTime($workingHour->open_time) : '09:00';
        $closeTimeStr = ($workingHour && !empty($workingHour->close_time)) ? $normalizeTime($workingHour->close_time) : '20:00';

        if (!$openTimeStr) $openTimeStr = '09:00';
        if (!$closeTimeStr) $closeTimeStr = '20:00';

        $openDt = Carbon::parse("{$dateStr} {$openTimeStr}:00");
        $closeDt = Carbon::parse("{$dateStr} {$closeTimeStr}:00");

        if ($closeDt <= $openDt) {
            $openDt = Carbon::parse("{$dateStr} 09:00:00");
            $closeDt = Carbon::parse("{$dateStr} 20:00:00");
        }

        // Duration for selected service or 30 min default
        $reqDuration = 30;
        if ($this->selectedServiceId) {
            $service = Service::find($this->selectedServiceId);
            if ($service && $service->duration_minutes > 0) {
                $reqDuration = (int) $service->duration_minutes;
            }
        }

        // Fetch existing bookings for this barber or shop on this day
        $bookingQuery = Booking::query()
            ->whereBetween('appointment_at', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ])
            ->where('status', '!=', 'cancelled');

        if ($this->selectedBarberId) {
            $bookingQuery->where('barber_id', $this->selectedBarberId);
        } else {
            $bookingQuery->where('barber_shop_id', $this->shop->id);
        }

        $bookings = $bookingQuery->with('service')->get();

        $bookingEntries = $bookings->map(function ($b) {
            $start = $b->appointment_at->copy();
            $dur = $b->service?->duration_minutes ?? 30;
            return [
                'start' => $start,
                'end' => $start->copy()->addMinutes($dur),
            ];
        });

        // Add lunch break if configured for workingHour
        $lunchStartStr = $normalizeTime($workingHour?->lunch_start);
        $lunchEndStr = $normalizeTime($workingHour?->lunch_end);
        if ($lunchStartStr && $lunchEndStr) {
            $lunchStartDt = Carbon::parse("{$dateStr} {$lunchStartStr}:00");
            $lunchEndDt = Carbon::parse("{$dateStr} {$lunchEndStr}:00");
            if ($lunchEndDt > $lunchStartDt) {
                $bookingEntries->push([
                    'start' => $lunchStartDt,
                    'end' => $lunchEndDt,
                ]);
            }
        }

        $slots = [];
        $cursor = $openDt->copy();

        while ($cursor < $closeDt) {
            $slotStart = $cursor->copy();
            $slotEnd = $slotStart->copy()->addMinutes($reqDuration);

            if ($slotEnd > $closeDt) {
                break;
            }

            // Skip past times if selected date is today
            if ($date->isToday() && $slotStart->isBefore($now)) {
                $cursor->addMinutes(30);
                continue;
            }

            // Check overlap with existing bookings
            $hasOverlap = false;
            foreach ($bookingEntries as $entry) {
                if ($slotStart < $entry['end'] && $slotEnd > $entry['start']) {
                    $hasOverlap = true;
                    break;
                }
            }

            if (!$hasOverlap) {
                $slots[] = $slotStart->format('H:i');
            }

            $cursor->addMinutes(30);
        }

        if (empty($this->bookingTime) || !in_array($this->bookingTime, $slots, true)) {
            $this->bookingTime = $slots[0] ?? null;
        }

        return $slots;
    }

    public function submitBooking()
    {
        $this->validate([
            'selectedBarberId' => 'required|exists:barbers,id',
            'selectedServiceId' => 'required|exists:services,id',
            'bookingDate' => 'required|date|after_or_equal:today',
            'bookingTime' => 'required',
            'customerName' => 'required|string|max:100',
            'customerPhone' => 'required|string|max:30',
            'notes' => 'nullable|string|max:80',
        ], [
            'customerName.required' => 'Ju lutemi vendosni Emrin dhe Mbiemrin tuaj.',
            'customerPhone.required' => 'Ju lutemi vendosni Numrin e Telefonit.',
            'bookingTime.required' => 'Ju lutemi zgjidhni një orar të lirë.',
            'bookingDate.after_or_equal' => 'Data e rezervimit duhet të jetë sot ose në ditët në vijim.',
        ]);

        try {
            $date = Carbon::parse($this->bookingDate);
        } catch (\Throwable $e) {
            $date = Carbon::today();
        }
        $cleanDate = $date->format('Y-m-d');

        $appointmentAt = Carbon::parse("{$cleanDate} {$this->bookingTime}:00");

        try {
            // Check overlap
            Booking::checkOverlap($this->selectedBarberId, $appointmentAt, $this->selectedServiceId);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('bookingTime', $e->getMessage());
            return;
        }

        // Find or create Customer
        $customer = Customer::updateOrCreate(
            [
                'barber_shop_id' => $this->shop->id,
                'phone' => trim($this->customerPhone),
            ],
            [
                'name' => trim($this->customerName),
                'total_bookings' => 0,
                'no_show_count' => 0,
            ]
        );

        $service = Service::findOrFail($this->selectedServiceId);

        // Create Booking
        $booking = Booking::create([
            'barber_shop_id' => $this->shop->id,
            'barber_id' => $this->selectedBarberId,
            'service_id' => $service->id,
            'customer_id' => $customer->id,
            'appointment_at' => $appointmentAt,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'total_price' => $service->price,
            'notes' => $this->notes ? "Rezervim Online: " . $this->notes : "Rezervim Online nga Faqja Publike",
            'source' => 'online',
        ]);

        $this->createdBooking = $booking;
        $this->bookingSuccess = true;
    }

    public function render()
    {
        return view('livewire.public-shop-booking', [
            'services' => $this->services,
            'barbers' => $this->barbers,
            'staff' => $this->barbers,
            'availableTimeSlots' => $this->availableTimeSlots,
        ])->layout('components.layouts.blank');
    }
}
