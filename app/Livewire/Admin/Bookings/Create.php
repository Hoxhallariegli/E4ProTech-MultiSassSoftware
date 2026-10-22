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
    public bool $send_sms = true;

    // Time Slots & Service Search Helpers
    public $booking_date = '';
    public $selected_time = '';
    public $service_search = '';

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
        $services = $this->getServiceListObjectsProperty();
        if ($services->count() === 1) {
            $this->selectService($services->first()->id);
        }
    }

    public function updatedBarberShopId($value)
    {
        $this->selected_time = '';
        $this->appointment_at = '';

        if ($this->barber_id) {
            $barber = Barber::find($this->barber_id);
            if (!$barber || (string)$barber->barber_shop_id !== (string)$value) {
                $this->barber_id = '';
            }
        }

        if ($this->service_id) {
            $service = Service::find($this->service_id);
            if (!$service || (string)$service->barber_shop_id !== (string)$value) {
                $this->service_id = '';
                $this->total_price = '';
            }
        }

        if ($this->customer_id) {
            $customer = Customer::find($this->customer_id);
            if (!$customer || (isset($customer->barber_shop_id) && $customer->barber_shop_id && (string)$customer->barber_shop_id !== (string)$value)) {
                $this->customer_id = '';
            }
        }

        $barbers = $this->getbarbersList();
        if (count($barbers) === 1) {
            $this->barber_id = (string) array_key_first($barbers);
        }

        $services = $this->getServiceListObjectsProperty();
        if ($services->count() === 1) {
            $this->selectService($services->first()->id);
        }
    }

    public function updatedBarberId($value)
    {
        $this->selected_time = '';
        $this->appointment_at = '';

        if (!$value) return;

        $related = Barber::find($value);
        if (!$related) return;

        if (isset($related->barber_shop_id) && (string)$this->barber_shop_id !== (string)$related->barber_shop_id) {
            $this->barber_shop_id = (string) $related->barber_shop_id;
            $this->updatedBarberShopId($related->barber_shop_id);
            $this->barber_id = (string) $value;
        }
    }

    public function selectService($serviceId)
    {
        $this->selected_time = '';
        $this->appointment_at = '';
        $this->service_id = $serviceId;

        $related = Service::find($serviceId);
        if ($related) {
            if (isset($related->barber_shop_id) && (string)$this->barber_shop_id !== (string)$related->barber_shop_id) {
                $this->barber_shop_id = (string) $related->barber_shop_id;
                $this->updatedBarberShopId($related->barber_shop_id);
                $this->service_id = (string) $serviceId;
            }
            if (isset($related->price)) {
                $this->total_price = $related->price;
            }
        }
    }

    public function updatedServiceId($value)
    {
        if (!$value) return;
        $this->selectService($value);
    }

    public function updatedCustomerId($value)
    {
        if (!$value) return;
        $related = Customer::find($value);
        if (!$related) return;

        if (isset($related->barber_shop_id) && $related->barber_shop_id && (string)$this->barber_shop_id !== (string)$related->barber_shop_id) {
            $this->barber_shop_id = (string) $related->barber_shop_id;
            $this->updatedBarberShopId($related->barber_shop_id);
            $this->customer_id = (string) $value;
        }
    }

    public function updatedBookingDate()
    {
        $this->selected_time = '';
        $this->appointment_at = '';

        try {
            $date = Carbon::parse($this->booking_date);
            if ($date->isBefore(now()->startOfDay())) {
                $this->send_sms = false;
            } else {
                $this->send_sms = true;
            }
        } catch (\Throwable $e) {}
    }

    public function selectQuickDate($days)
    {
        $this->booking_date = now()->addDays($days)->format('Y-m-d');
        $this->selected_time = '';
        $this->appointment_at = '';

        if ($days < 0) {
            $this->send_sms = false;
        } else {
            $this->send_sms = true;
        }
    }

    public function selectTimeSlot($time)
    {
        $this->selected_time = $time;
        if ($this->booking_date && $time) {
            $this->appointment_at = $this->booking_date . 'T' . $time;

            // Auto-check/uncheck send_sms based on whether selected time is in the past
            $shop = BarberShop::find($this->barber_shop_id);
            $tz = ($shop && $shop->timezone) ? $shop->timezone : config('app.timezone', 'Europe/Tirane');

            try {
                $dt = Carbon::parse($this->booking_date . ' ' . $time . ':00', $tz);
                if ($dt->isPast()) {
                    $this->send_sms = false;
                } else {
                    $this->send_sms = true;
                }
            } catch (\Throwable $e) {}
        }
    }

    public function getServiceListObjectsProperty()
    {
        $query = Service::query();
        if ($this->barber_shop_id) {
            $query->where('barber_shop_id', $this->barber_shop_id);
        } elseif (method_exists(Service::class, 'scopeForActiveShop')) {
            $query->forActiveShop();
        }
        $query->where('active', true);

        if (!empty($this->service_search)) {
            $query->where('name', 'like', '%' . $this->service_search . '%');
        }

        return $query->get();
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

        $tz = config('app.timezone', 'Europe/Tirane');
        if ($shop->timezone) {
            $tz = $shop->timezone;
        }

        $slotStep = $shop->resolved_min_service_time;
        if ($slotStep < 1) $slotStep = 15;

        $now = Carbon::now($tz);

        try {
            $date = Carbon::parse($this->booking_date, $tz);
        } catch (\Throwable $e) {
            $date = Carbon::today($tz);
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

        $openDt = Carbon::parse("{$dateStr} {$openTimeStr}:00", $tz);
        $closeDt = Carbon::parse("{$dateStr} {$closeTimeStr}:00", $tz);

        if ($closeDt <= $openDt) {
            $openDt = Carbon::parse("{$dateStr} 09:00:00", $tz);
            $closeDt = Carbon::parse("{$dateStr} 20:00:00", $tz);
        }

        // Required duration for selected service
        $reqDuration = $slotStep;
        if ($this->service_id) {
            $service = Service::find($this->service_id);
            if ($service && $service->duration_minutes > 0) {
                $reqDuration = (int) $service->duration_minutes;
            }
        }

        // Timezone-safe start and end of day query in UTC
        $startOfDayUtc = $date->copy()->startOfDay()->setTimezone('UTC');
        $endOfDayUtc = $date->copy()->endOfDay()->setTimezone('UTC');

        $bookingQuery = Booking::query()
            ->whereBetween('appointment_at', [$startOfDayUtc, $endOfDayUtc])
            ->where('status', '!=', 'cancelled');

        if ($this->barber_id) {
            $bookingQuery->where('barber_id', $this->barber_id);
        } else {
            $bookingQuery->where('barber_shop_id', $this->barber_shop_id);
        }

        $bookings = $bookingQuery->with(['customer', 'service', 'barber'])->get();

        $activeBarbersCount = Barber::where('barber_shop_id', $this->barber_shop_id)->where('active', true)->count();
        if ($activeBarbersCount < 1) $activeBarbersCount = 1;

        $bookingEntries = [];
        foreach ($bookings as $b) {
            $start = $b->appointment_at->copy()->setTimezone($tz);
            $dur = $b->service?->duration_minutes ?? 30;
            $startMinutes = ($start->hour * 60) + $start->minute;
            $endMinutes = $startMinutes + $dur;

            $bookingEntries[] = [
                'type' => 'booking',
                'title' => ($b->customer?->name ?? 'Klient') . ' (' . ($b->service?->name ?? 'Shërbim') . ')',
                'start_m' => $startMinutes,
                'end_m' => $endMinutes,
                'start_str' => $start->format('H:i'),
                'end_str' => $start->copy()->addMinutes($dur)->format('H:i'),
            ];
        }

        // Add lunch break
        $lunchStartStr = $normalizeTime($workingHour?->lunch_start);
        $lunchEndStr = $normalizeTime($workingHour?->lunch_end);
        if ($lunchStartStr && $lunchEndStr) {
            $lunchStartDt = Carbon::parse("{$dateStr} {$lunchStartStr}:00", $tz);
            $lunchEndDt = Carbon::parse("{$dateStr} {$lunchEndStr}:00", $tz);
            if ($lunchEndDt > $lunchStartDt) {
                $lStartM = ($lunchStartDt->hour * 60) + $lunchStartDt->minute;
                $lEndM = ($lunchEndDt->hour * 60) + $lunchEndDt->minute;
                $bookingEntries[] = [
                    'type' => 'break',
                    'title' => 'Pushimi i Drekës',
                    'start_m' => $lStartM,
                    'end_m' => $lEndM,
                    'start_str' => $lunchStartDt->format('H:i'),
                    'end_str' => $lunchEndDt->format('H:i'),
                ];
            }
        }

        // Add staff absence / bulk cancellation periods
        if ($this->barber_id) {
            $bulkCancellations = \App\Models\BulkCancellation::withoutGlobalScope('barber_shop_access')
                ->where('barber_id', $this->barber_id)
                ->where('start_at', '<', $endOfDayUtc)
                ->where('end_at', '>', $startOfDayUtc)
                ->get();

            foreach ($bulkCancellations as $bc) {
                $bcStart = $bc->start_at->copy()->setTimezone($tz);
                $bcEnd = $bc->end_at->copy()->setTimezone($tz);
                $bcStartM = ($bcStart->hour * 60) + $bcStart->minute;
                $bcEndM = ($bcEnd->hour * 60) + $bcEnd->minute;

                $bookingEntries[] = [
                    'type' => 'break',
                    'title' => 'Mungesë Stafi: ' . ($bc->reason ?: 'Pushim Emergjent'),
                    'start_m' => $bcStartM,
                    'end_m' => $bcEndM,
                    'start_str' => $bcStart->format('H:i'),
                    'end_str' => $bcEnd->format('H:i'),
                ];
            }
        }

        $closeMinutes = ($closeDt->hour * 60) + $closeDt->minute;
        $nowMinutes = ($now->hour * 60) + $now->minute;

        $slots = [];
        $cursor = $openDt->copy();

        while ($cursor < $closeDt) {
            $slotStartM = ($cursor->hour * 60) + $cursor->minute;
            $slotEndM = $slotStartM + $reqDuration;

            $timeStr = $cursor->format('H:i');
            $endTimeStr = $cursor->copy()->addMinutes($reqDuration)->format('H:i');

            $exceedsClose = ($slotEndM > $closeMinutes);

            // Correct past logic: Entire date in the past OR slot time today earlier than now
            $isPastDate = $date->isBefore($now->copy()->startOfDay());
            $isPastTime = ($date->isToday() && $slotStartM < $nowMinutes);
            $isPast = $isPastDate || $isPastTime;

            $bookingsInSlotCount = 0;
            $overlapReason = null;

            foreach ($bookingEntries as $entry) {
                if ($slotStartM < $entry['end_m'] && $slotEndM > $entry['start_m']) {
                    $bookingsInSlotCount++;
                    if (!$overlapReason) {
                        $overlapReason = $entry['title'] . " (" . $entry['start_str'] . "-" . $entry['end_str'] . ")";
                    }
                }
            }

            // Max allowed simultaneous bookings in a slot
            $maxAllowedBookings = $this->barber_id ? 1 : $activeBarbersCount;
            $isOverbooked = ($bookingsInSlotCount >= $maxAllowedBookings);

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
            'serviceListObjects' => $this->getServiceListObjectsProperty(),
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

        // Pass send_sms flag into request so SendFirebaseNotificationListener processes it
        request()->merge(['send_sms' => $this->send_sms]);

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
