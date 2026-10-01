<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BarberShop;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\WorkingHour;
use App\Services\NotificationRouter;
use App\Events\BookingChanged;
use Carbon\Carbon;

class PublicShopBooking extends Component
{
    public BarberShop $shop;

    public ?int $selectedBarberId = null;
    public ?int $selectedServiceId = null;
    public string $bookingDate = '';
    public string $bookingTime = '';
    public string $customerName = '';
    public string $customerPhone = '';
    public string $notes = '';

    public bool $bookingSuccess = false;
    public ?int $createdBookingId = null;

    public function mount(BarberShop $shop)
    {
        $this->shop = $shop;
        $this->bookingDate = now()->format('Y-m-d');

        $firstBarber = Barber::where('barber_shop_id', $this->shop->id)->where('active', true)->first();
        if ($firstBarber) {
            $this->selectedBarberId = $firstBarber->id;
        }

        $firstService = Service::where('barber_shop_id', $this->shop->id)->where('active', true)->first();
        if ($firstService) {
            $this->selectedServiceId = $firstService->id;
        }

        $slots = $this->availableSlots;
        if (!empty($slots)) {
            $this->bookingTime = $slots[0];
        }
    }

    public function selectService($serviceId)
    {
        $this->selectedServiceId = (int) $serviceId;
        $this->autoSelectFirstAvailableSlot();
    }

    public function selectBarber($barberId)
    {
        $this->selectedBarberId = (int) $barberId;
        $this->autoSelectFirstAvailableSlot();
    }

    public function updatedBookingDate()
    {
        $this->autoSelectFirstAvailableSlot();
    }

    private function autoSelectFirstAvailableSlot()
    {
        $slots = $this->availableSlots;
        if (!empty($slots) && !in_array($this->bookingTime, $slots, true)) {
            $this->bookingTime = $slots[0];
        } elseif (empty($slots)) {
            $this->bookingTime = '';
        }
    }

    public function getAvailableSlotsProperty(): array
    {
        if (!$this->selectedBarberId || !$this->bookingDate) {
            return [];
        }

        try {
            $date = Carbon::parse($this->bookingDate);
        } catch (\Throwable $e) {
            return [];
        }

        $dayName = $date->format('l');

        $workingHour = WorkingHour::where('barber_id', $this->selectedBarberId)
            ->where('day_of_week', $dayName)
            ->first();

        if ($workingHour && $workingHour->is_closed) {
            return [];
        }

        $normalizeTime = function ($val) {
            if (!$val) return null;
            try {
                return Carbon::parse((string) $val)->format('H:i');
            } catch (\Throwable $e) {
                return null;
            }
        };

        $openTimeStr = $normalizeTime($workingHour?->open_time) ?? '08:00';
        $closeTimeStr = $normalizeTime($workingHour?->close_time) ?? '20:00';

        $openDt = Carbon::parse("{$this->bookingDate} {$openTimeStr}:00");
        $closeDt = Carbon::parse("{$this->bookingDate} {$closeTimeStr}:00");

        if ($closeDt <= $openDt) {
            return [];
        }

        $lunchStartStr = $normalizeTime($workingHour?->lunch_start);
        $lunchEndStr = $normalizeTime($workingHour?->lunch_end);

        $lunchStartDt = null;
        $lunchEndDt = null;
        if ($lunchStartStr && $lunchEndStr) {
            $lunchStartDt = Carbon::parse("{$this->bookingDate} {$lunchStartStr}:00");
            $lunchEndDt = Carbon::parse("{$this->bookingDate} {$lunchEndStr}:00");
            if ($lunchEndDt <= $lunchStartDt) {
                $lunchStartDt = null;
                $lunchEndDt = null;
            }
        }

        // Min service time for this shop ID
        $minServiceTime = $this->shop->resolved_min_service_time;

        $reqDuration = $minServiceTime;
        if ($this->selectedServiceId) {
            $service = Service::find($this->selectedServiceId);
            if ($service && $service->duration_minutes > 0) {
                $reqDuration = (int) $service->duration_minutes;
            }
        }

        $bookings = Booking::query()
            ->where('barber_id', $this->selectedBarberId)
            ->whereBetween('appointment_at', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ])
            ->where('status', '!=', 'cancelled')
            ->with('service')
            ->get();

        $bookingEntries = $bookings->map(function ($b) {
            $start = $b->appointment_at->copy();
            $dur = $b->service?->duration_minutes ?? 30;
            return [
                'start' => $start,
                'end' => $start->copy()->addMinutes($dur),
            ];
        });

        if ($lunchStartDt && $lunchEndDt) {
            $bookingEntries->push([
                'start' => $lunchStartDt->copy(),
                'end' => $lunchEndDt->copy(),
            ]);
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
            if ($date->isToday() && $slotStart->isPast()) {
                $cursor->addMinutes($minServiceTime);
                continue;
            }

            // Check overlap
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

            $cursor->addMinutes($minServiceTime);
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
        ], [
            'customerName.required' => 'Ju lutemi vendosni Emrin dhe Mbiemrin tuaj.',
            'customerPhone.required' => 'Ju lutemi vendosni Numrin e Telefonit.',
            'bookingTime.required' => 'Ju lutemi zgjidhni një orar të lirë.',
            'bookingDate.after_or_equal' => 'Data e rezervimit duhet të jetë sot ose në ditët në vijim.',
        ]);

        $appointmentAt = Carbon::parse("{$this->bookingDate} {$this->bookingTime}:00");

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

        $customer->increment('total_bookings');

        // Trigger Notification Router explicitly (Deduplicated automatically by NotificationRouter)
        try {
            app(NotificationRouter::class)->maybeNotify('bookings.created', $booking, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to trigger NotificationRouter from Livewire: " . $e->getMessage());
        }

        $this->createdBookingId = $booking->id;
        $this->bookingSuccess = true;
    }

    public function resetForm()
    {
        $this->bookingSuccess = false;
        $this->createdBookingId = null;
        $this->customerName = '';
        $this->customerPhone = '';
        $this->notes = '';
        $this->autoSelectFirstAvailableSlot();
    }

    public function render()
    {
        $staff = Barber::where('barber_shop_id', $this->shop->id)->where('active', true)->get();
        $services = Service::where('barber_shop_id', $this->shop->id)->where('active', true)->get();
        $createdBooking = $this->createdBookingId ? Booking::with(['barber', 'service', 'barberShop'])->find($this->createdBookingId) : null;

        return view('livewire.public-shop-booking', [
            'staff' => $staff,
            'services' => $services,
            'createdBooking' => $createdBooking,
        ]);
    }
}
