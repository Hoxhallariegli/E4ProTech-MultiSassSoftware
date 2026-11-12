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

    public int $currentStep = 1;

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

        $this->autoAdvanceToAvailableDate();
    }

    #[\Livewire\Attributes\On('preselect-service')]
    public function preselectService($serviceId)
    {
        $this->selectService($serviceId);
    }

    public function selectService($serviceId)
    {
        $this->selectedServiceId = $serviceId;
        $this->bookingTime = null;
        $this->currentStep = 2; // Auto advance to Step 2 (Staff)
        $this->autoAdvanceToAvailableDate();
    }

    public function selectBarber($barberId)
    {
        $this->selectedBarberId = $barberId;
        $this->bookingTime = null;
        $this->currentStep = 3; // Auto advance to Step 3 (Date & Time)
        $this->autoAdvanceToAvailableDate();
    }

    public function goToStep(int $step)
    {
        if ($step < 1) $step = 1;
        if ($step > 4) $step = 4;
        $this->currentStep = $step;
        if ($step === 3) {
            $this->autoAdvanceToAvailableDate();
        }
    }

    public function nextStep()
    {
        if ($this->currentStep === 1 && !$this->selectedServiceId) {
            $this->addError('selectedServiceId', 'Ju lutemi zgjidhni një shërbim.');
            return;
        }
        if ($this->currentStep === 2 && !$this->selectedBarberId) {
            $this->addError('selectedBarberId', 'Ju lutemi zgjidhni një anëtar stafit.');
            return;
        }
        if ($this->currentStep === 3 && (!$this->bookingDate || !$this->bookingTime)) {
            $this->addError('bookingTime', 'Ju lutemi zgjidhni datën dhe orarin.');
            return;
        }
        $this->currentStep = min(4, $this->currentStep + 1);
        if ($this->currentStep === 3) {
            $this->autoAdvanceToAvailableDate();
        }
    }

    public function previousStep()
    {
        $this->currentStep = max(1, $this->currentStep - 1);
        if ($this->currentStep === 3) {
            $this->autoAdvanceToAvailableDate();
        }
    }

    public function selectBookingDate(string $dateStr): void
    {
        $this->bookingDate = $dateStr;
        $this->bookingTime = null;
        $slots = $this->getTimeSlotsForDate($dateStr);
        if (!empty($slots)) {
            $this->bookingTime = $slots[0];
        }
    }

    public function getAvailableDatesProperty(): array
    {
        $dates = [];
        $isEn = app()->getLocale() === 'en';
        $today = Carbon::now();

        $albanianDays = [
            'Monday' => 'Hën',
            'Tuesday' => 'Mar',
            'Wednesday' => 'Mër',
            'Thursday' => 'Enj',
            'Friday' => 'Pre',
            'Saturday' => 'Sht',
            'Sunday' => 'Die',
        ];

        $englishDays = [
            'Monday' => 'Mo',
            'Tuesday' => 'Tu',
            'Wednesday' => 'We',
            'Thursday' => 'Th',
            'Friday' => 'Fr',
            'Saturday' => 'Sa',
            'Sunday' => 'Su',
        ];

        for ($i = 0; $i < 7; $i++) {
            $dt = $today->copy()->addDays($i);
            $dateStr = $dt->format('Y-m-d');
            $dayName = $dt->format('l');

            if ($i === 0) {
                $dayLabel = $isEn ? 'Today' : 'Sot';
            } elseif ($i === 1) {
                $dayLabel = $isEn ? 'Tomorrow' : 'Nesër';
            } else {
                $dayLabel = $isEn ? ($englishDays[$dayName] ?? 'Day') : ($albanianDays[$dayName] ?? 'Ditë');
            }

            $dates[] = [
                'date' => $dateStr,
                'day_num' => $dt->format('j'),
                'formatted' => $dt->format('d/m'),
                'day' => $dayLabel,
            ];
        }

        return $dates;
    }

    public function updatedBookingDate()
    {
        $this->bookingTime = null;
        $slots = $this->getTimeSlotsForDate($this->bookingDate ?: Carbon::now()->format('Y-m-d'));
        if (!empty($slots)) {
            $this->bookingTime = $slots[0];
        }
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
        $this->selectedServiceId = null;
        $this->selectedBarberId = null;
        $this->currentStep = 1;

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

        $this->autoAdvanceToAvailableDate();
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

    public function autoAdvanceToAvailableDate(): void
    {
        if (!$this->bookingDate) {
            $this->bookingDate = Carbon::now()->format('Y-m-d');
        }

        // Check if current date has slots
        $currentSlots = $this->getTimeSlotsForDate($this->bookingDate);
        if (!empty($currentSlots)) {
            if (empty($this->bookingTime) || !in_array($this->bookingTime, $currentSlots, true)) {
                $this->bookingTime = $currentSlots[0];
            }
            return;
        }

        // Scan ahead up to 14 days to find the first open date
        $startDate = Carbon::parse($this->bookingDate);
        for ($i = 1; $i <= 14; $i++) {
            $checkDate = $startDate->copy()->addDays($i)->format('Y-m-d');
            $slots = $this->getTimeSlotsForDate($checkDate);
            if (!empty($slots)) {
                $this->bookingDate = $checkDate;
                $this->bookingTime = $slots[0];
                return;
            }
        }
    }

    public function getAvailableTimeSlotsProperty()
    {
        $this->autoAdvanceToAvailableDate();
        return $this->getTimeSlotsForDate($this->bookingDate ?: Carbon::now()->format('Y-m-d'));
    }

    public function getTimeSlotsForDate(string $dateStr): array
    {
        $now = Carbon::now();

        try {
            $date = Carbon::parse($dateStr);
        } catch (\Throwable $e) {
            $date = Carbon::today();
        }

        $dayIndex = $date->dayOfWeek;
        $dayEnglish = strtolower($date->format('l'));
        $dayAlbanianMap = [
            'monday' => 'e hene',
            'tuesday' => 'e marte',
            'wednesday' => 'e merkure',
            'thursday' => 'e enjte',
            'friday' => 'e premte',
            'saturday' => 'e shtune',
            'sunday' => 'e diel',
        ];
        $dayAlbanian = $dayAlbanianMap[$dayEnglish] ?? $dayEnglish;

        $workingHour = null;

        if ($this->selectedBarberId) {
            $workingHour = WorkingHour::withoutGlobalScope('barber_shop_access')
                ->where('barber_id', $this->selectedBarberId)
                ->where(function($q) use ($dayEnglish, $dayAlbanian, $dayIndex) {
                    $q->whereRaw('LOWER(day_of_week) LIKE ?', ["%{$dayEnglish}%"])
                      ->orWhereRaw('LOWER(day_of_week) LIKE ?', ["%{$dayAlbanian}%"])
                      ->orWhere('day_of_week', $dayIndex);
                })
                ->first();

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

        $reqDuration = 30;
        if ($this->selectedServiceId) {
            $service = Service::withoutGlobalScope('barber_shop_access')->find($this->selectedServiceId);
            if ($service && $service->duration_minutes > 0) {
                $reqDuration = (int) $service->duration_minutes;
            }
        }

        $bookingQuery = Booking::withoutGlobalScope('barber_shop_access')
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

        $bookings = $bookingQuery->get();

        $bookingEntries = $bookings->map(function ($b) {
            $start = $b->appointment_at->copy();
            $service = Service::withoutGlobalScope('barber_shop_access')->find($b->service_id);
            $dur = ($service && $service->duration_minutes > 0) ? (int) $service->duration_minutes : 30;
            return [
                'start' => $start,
                'end' => $start->copy()->addMinutes($dur),
            ];
        });

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

        if ($this->selectedBarberId) {
            $absences = \App\Models\BulkCancellation::where('barber_id', $this->selectedBarberId)
                ->where('start_at', '<', $date->copy()->endOfDay())
                ->where('end_at', '>', $date->copy()->startOfDay())
                ->get();

            foreach ($absences as $abs) {
                $absStart = $abs->start_at instanceof Carbon ? $abs->start_at : Carbon::parse($abs->start_at);
                $absEnd = $abs->end_at instanceof Carbon ? $abs->end_at : Carbon::parse($abs->end_at);
                if ($absEnd > $absStart) {
                    $bookingEntries->push([
                        'start' => $absStart,
                        'end' => $absEnd,
                    ]);
                }
            }
        }

        $slotStep = $reqDuration > 0 ? $reqDuration : 15;
        if ($slotStep <= 0) $slotStep = 15;

        $slots = [];
        $cursor = $openDt->copy();

        while ($cursor < $closeDt) {
            $slotStart = $cursor->copy();
            $slotEnd = $slotStart->copy()->addMinutes($reqDuration);

            if ($slotEnd > $closeDt) {
                break;
            }

            if ($date->isToday() && $slotStart->isBefore($now)) {
                $cursor->addMinutes($slotStep);
                continue;
            }

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

            $cursor->addMinutes($slotStep);
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

        // Find or create Customer (bypassing global scope if visitor is logged in to another shop)
        $customer = Customer::withoutGlobalScope('barber_shop_access')
            ->where('barber_shop_id', $this->shop->id)
            ->where('phone', trim($this->customerPhone))
            ->first();

        if (!$customer) {
            $customer = Customer::withoutGlobalScope('barber_shop_access')->create([
                'barber_shop_id' => $this->shop->id,
                'phone' => trim($this->customerPhone),
                'name' => trim($this->customerName),
                'total_bookings' => 0,
                'no_show_count' => 0,
            ]);
        } else {
            $customer->update(['name' => trim($this->customerName)]);
        }

        $service = Service::withoutGlobalScope('barber_shop_access')->findOrFail($this->selectedServiceId);

        // Create Booking (bypassing global scope if visitor is logged in to another shop)
        $booking = Booking::withoutGlobalScope('barber_shop_access')->create([
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
            'availableDates' => $this->availableDates,
            'availableTimeSlots' => $this->availableTimeSlots,
        ])->layout('components.layouts.blank');
    }
}
