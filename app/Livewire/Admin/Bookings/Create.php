<?php

namespace App\Livewire\Admin\Bookings;

use App\Models\Booking;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Customer;
use App\Models\BarberShop;
use App\Models\WorkingHour;
use App\Domain\Booking\DTOs\BookingDTO;
use App\Domain\Booking\Actions\CreateBookingAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Carbon\Carbon;

#[Title('Add Booking')]
class Create extends Component
{
    use WithPagination;

    public $barber_shop_id = '';
    public $barber_id = '';
    public $service_id = '';
    public $customer_id = '';
    public $appointment_at = '';
    public $status = 'confirmed';
    public $total_price = '';
    public $notes = '';
    public $source = 'walk-in';

    // Time Slots Helpers
    public $booking_date = '';
    public $selected_time = '';

    #[On('barber-shop-created')]
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }

    #[On('barber-created')]
    public function refreshBarbers($id) { $this->barber_id = $id; $this->updatedBarberId($id); }

    #[On('service-created')]
    public function refreshServices($id) { $this->service_id = $id; $this->updatedServiceId($id); }

    #[On('customer-created')]
    public function refreshCustomers($id) { $this->customer_id = $id; $this->updatedCustomerId($id); }

    public function mount()
    {
        if (auth()->check() && auth()->user()->barber_shop_id) {
            $this->barber_shop_id = auth()->user()->barber_shop_id;
        }

        $this->booking_date = now()->format('Y-m-d');
        $this->status = 'confirmed';
        $this->source = 'walk-in';

        // Preselect barber if only 1 exists
        $barbers = $this->getbarbersList();
        if (count($barbers) === 1) {
            $this->barber_id = (string) array_key_first($barbers);
        }

        // Preselect service if only 1 exists
        $services = $this->getservicesList();
        if (count($services) === 1) {
            $this->service_id = (string) array_key_first($services);
            $service = Service::find($this->service_id);
            if ($service) {
                $this->total_price = $service->price;
            }
        }
    }

    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $this->selected_time = '';
        $this->appointment_at = '';
    }

    public function updatedBarberId($value)
    {
        if (!$value) return;
        $related = Barber::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id) && !$this->barber_shop_id) {
            $this->barber_shop_id = $related->barber_shop_id;
        }
        $this->selected_time = '';
        $this->appointment_at = '';
    }

    public function updatedServiceId($value)
    {
        if (!$value) return;
        $related = Service::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id) && !$this->barber_shop_id) {
            $this->barber_shop_id = $related->barber_shop_id;
        }

        // Auto-fill price
        if (isset($related->price)) {
            $this->total_price = $related->price;
        }

        $this->selected_time = '';
        $this->appointment_at = '';
    }

    public function updatedCustomerId($value)
    {
        if (!$value) return;
        $related = Customer::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id) && !$this->barber_shop_id) {
            $this->barber_shop_id = $related->barber_shop_id;
        }
    }

    public function updatedBookingDate()
    {
        $this->selected_time = '';
        $this->appointment_at = '';
    }

    public function selectQuickDate($days)
    {
        $this->booking_date = now()->addDays($days)->format('Y-m-d');
        $this->selected_time = '';
        $this->appointment_at = '';
    }

    public function selectTimeSlot($time)
    {
        $this->selected_time = $time;
        if ($this->booking_date && $time) {
            $this->appointment_at = $this->booking_date . 'T' . $time;
        }
    }

    public function getAvailableTimeSlotsProperty()
    {
        if (!$this->booking_date || !$this->barber_shop_id) {
            return [
                'is_closed' => false,
                'slot_step' => 15,
                'req_duration' => 30,
                'slots' => [],
            ];
        }

        $shop = BarberShop::find($this->barber_shop_id);
        if (!$shop) {
            return [
                'is_closed' => false,
                'slot_step' => 15,
                'req_duration' => 30,
                'slots' => [],
            ];
        }

        // Get step interval from min_service_time (resolved_min_service_time)
        $slotStep = $shop->resolved_min_service_time;
        if ($slotStep < 5) $slotStep = 15;

        $now = Carbon::now();

        try {
            $date = Carbon::parse($this->booking_date);
        } catch (\Throwable $e) {
            $date = Carbon::today();
        }
        $dateStr = $date->format('Y-m-d');

        $workingHour = null;

        if ($this->barber_id) {
            $dayOfWeekLower = strtolower($date->format('l'));
            $workingHour = WorkingHour::where('barber_id', $this->barber_id)
                ->where(function($q) use ($dayOfWeekLower) {
                    $q->whereRaw('LOWER(day_of_week) = ?', [$dayOfWeekLower]);
                })
                ->first();

            if ($workingHour && (bool)$workingHour->is_closed === true) {
                return [
                    'is_closed' => true,
                    'slot_step' => $slotStep,
                    'req_duration' => 30,
                    'slots' => [],
                ];
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

        // Required duration for selected service
        $reqDuration = 30;
        if ($this->service_id) {
            $service = Service::find($this->service_id);
            if ($service && $service->duration_minutes > 0) {
                $reqDuration = (int) $service->duration_minutes;
            }
        }

        // Fetch existing bookings for this barber/shop on this day
        $bookingQuery = Booking::query()
            ->whereBetween('appointment_at', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ])
            ->where('status', '!=', 'cancelled');

        if ($this->barber_id) {
            $bookingQuery->where('barber_id', $this->barber_id);
        } else {
            $bookingQuery->where('barber_shop_id', $this->barber_shop_id);
        }

        $bookings = $bookingQuery->with(['customer', 'service', 'barber'])->get();

        $bookingEntries = $bookings->map(function ($b) {
            $start = $b->appointment_at->copy();
            $dur = $b->service?->duration_minutes ?? 30;
            return [
                'type' => 'booking',
                'title' => ($b->customer?->name ?? 'Klient') . ' (' . ($b->service?->name ?? 'Shërbim') . ')',
                'start' => $start,
                'end' => $start->copy()->addMinutes($dur),
            ];
        });

        // Add lunch break
        $lunchStartStr = $normalizeTime($workingHour?->lunch_start);
        $lunchEndStr = $normalizeTime($workingHour?->lunch_end);
        if ($lunchStartStr && $lunchEndStr) {
            $lunchStartDt = Carbon::parse("{$dateStr} {$lunchStartStr}:00");
            $lunchEndDt = Carbon::parse("{$dateStr} {$lunchEndStr}:00");
            if ($lunchEndDt > $lunchStartDt) {
                $bookingEntries->push([
                    'type' => 'break',
                    'title' => 'Pushimi i Drekës',
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

            $timeStr = $slotStart->format('H:i');
            $endTimeStr = $slotEnd->format('H:i');

            $exceedsClose = ($slotEnd > $closeDt);
            $isPast = ($date->isToday() && $slotStart->isBefore($now));

            $overlapReason = null;
            $isOverbooked = false;

            foreach ($bookingEntries as $entry) {
                if ($slotStart < $entry['end'] && $slotEnd > $entry['start']) {
                    $isOverbooked = true;
                    $overlapReason = $entry['title'] . " (" . $entry['start']->format('H:i') . "-" . $entry['end']->format('H:i') . ")";
                    break;
                }
            }

            $isAvailable = !$exceedsClose && !$isPast && !$isOverbooked;

            $slots[] = [
                'time' => $timeStr,
                'end_time' => $endTimeStr,
                'is_available' => $isAvailable,
                'is_past' => $isPast,
                'is_overbooked' => $isOverbooked,
                'exceeds_close' => $exceedsClose,
                'reason' => $overlapReason ?? ($isPast ? 'Kaluar' : ($exceedsClose ? 'Kalim orari mbylljeje' : null)),
            ];

            $cursor->addMinutes($slotStep);
        }

        return [
            'is_closed' => false,
            'slot_step' => $slotStep,
            'req_duration' => $reqDuration,
            'slots' => $slots,
        ];
    }

    protected function getbarberShopsList() {
        $query = BarberShop::query();
        if (method_exists(BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    protected function getbarbersList() {
        $query = Barber::query();
        if ($this->barber_shop_id) {
            $query->where('barber_shop_id', $this->barber_shop_id);
        } elseif (method_exists(Barber::class, 'scopeForActiveShop')) {
            $query->forActiveShop();
        }
        return $query->where('active', true)->pluck('name', 'id')->toArray();
    }

    protected function getservicesList() {
        $query = Service::query();
        if ($this->barber_shop_id) {
            $query->where('barber_shop_id', $this->barber_shop_id);
        } elseif (method_exists(Service::class, 'scopeForActiveShop')) {
            $query->forActiveShop();
        }
        return $query->where('active', true)->pluck('name', 'id')->toArray();
    }

    protected function getcustomersList() {
        $query = Customer::query();
        if ($this->barber_shop_id) {
            $query->where('barber_shop_id', $this->barber_shop_id);
        } elseif (method_exists(Customer::class, 'scopeForActiveShop')) {
            $query->forActiveShop();
        }
        return $query->pluck('name', 'id')->toArray();
    }

    public function render()
    {
        abort_if_cannot('add_bookings');

        return view('livewire.admin.bookings.create', [
            'barberShops' => $this->getbarberShopsList(),
            'barbers' => $this->getbarbersList(),
            'services' => $this->getservicesList(),
            'customers' => $this->getcustomersList(),
            'timeSlotData' => $this->availableTimeSlots,
        ])->layout('components.layouts.app');
    }

    public function store(CreateBookingAction $action)
    {
        if (!$this->appointment_at && $this->booking_date && $this->selected_time) {
            $this->appointment_at = $this->booking_date . ' ' . $this->selected_time . ':00';
        }

        $this->validate();

        $dto = BookingDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'service_id' => $this->service_id,
            'customer_id' => $this->customer_id,
            'appointment_at' => $this->appointment_at,
            'status' => $this->status ?: 'confirmed',
            'total_price' => $this->total_price,
            'notes' => $this->notes,
            'source' => $this->source ?: 'walk-in',
        ]);

        $action->execute($dto);
        session()->flash('success', __('bookings.created'));
        return to_route('admin.bookings.index');
    }

    protected function rules(): array
    {
        return Booking::rules();
    }
}
