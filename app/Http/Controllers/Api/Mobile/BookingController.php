<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\BookingResource;
use \App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Domain\Booking\DTOs\BookingDTO;
use App\Domain\Booking\Actions\CreateBookingAction;
use App\Domain\Booking\Actions\UpdateBookingAction;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        abort_if_cannot('view_bookings');

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = Booking::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = Booking::query()->with(array (
  0 => 'barberShop',
  1 => 'barber',
  2 => 'service',
  3 => 'customer',
));

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
                foreach (array (
  0 => 'notes',
) as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        foreach ($request->all() as $key => $value) {
            if ($value === null || $value === '' || !str_ends_with($key, '_id')) {
                continue;
            }
            if (in_array($key, array (
), true)) {
                continue;
            }
            if (in_array($key, array_keys(Booking::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return BookingResource::collection($items);
    }

    public function show($id)
    {
        abort_if_cannot('view_bookings');
        $item = Booking::with(array (
  0 => 'barberShop',
  1 => 'barber',
  2 => 'service',
  3 => 'customer',
))->findOrFail($id);
        return new BookingResource($item);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_bookings');
        $data = $this->prepareData($request);

        if (empty($data['barber_shop_id'])) {
            if ($request->user()?->barber_shop_id) {
                $data['barber_shop_id'] = $request->user()->barber_shop_id;
            } elseif (!empty($data['barber_id'])) {
                $data['barber_shop_id'] = \App\Models\Barber::find($data['barber_id'])?->barber_shop_id;
            } elseif (!empty($data['service_id'])) {
                $data['barber_shop_id'] = \App\Models\Service::find($data['service_id'])?->barber_shop_id;
            }
        }

        $allowLunchOverride = (bool) ($request->input('override_lunch') || $request->input('allow_lunch_override'));
        $validated = validator($data, Booking::rules())->validate();
        $item = app(\App\Domain\Booking\Actions\CreateBookingAction::class)->execute(\App\Domain\Booking\DTOs\BookingDTO::fromArray($validated), $allowLunchOverride);
        return (new BookingResource($item->loadMissing(array (
  0 => 'barberShop',
  1 => 'barber',
  2 => 'service',
  3 => 'customer',
))))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_bookings');
        $item = Booking::findOrFail($id);

        $existing = [
            'barber_shop_id' => $item->barber_shop_id,
            'barber_id' => $item->barber_id,
            'service_id' => $item->service_id,
            'customer_id' => $item->customer_id,
            'appointment_at' => $item->appointment_at?->format('Y-m-d H:i:s'),
            'status' => $item->status,
            'total_price' => $item->total_price,
            'notes' => $item->notes,
            'source' => $item->source,
        ];

        $data = array_merge($existing, $this->prepareData($request));

        if (empty($data['barber_shop_id']) && $request->user()?->barber_shop_id) {
            $data['barber_shop_id'] = $request->user()->barber_shop_id;
        }

        $allowLunchOverride = (bool) ($request->input('override_lunch') || $request->input('allow_lunch_override'));
        $validated = validator($data, Booking::rules($id))->validate();
        $item = app(\App\Domain\Booking\Actions\UpdateBookingAction::class)->execute($item, \App\Domain\Booking\DTOs\BookingDTO::fromArray($validated), $allowLunchOverride);
        return new BookingResource($item->loadMissing(array (
  0 => 'barberShop',
  1 => 'barber',
  2 => 'service',
  3 => 'customer',
)));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_bookings');

        try {
            $item = Booking::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'Booking deleted.']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Delete Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Record is referenced by other data and cannot be deleted.',
            ], 409);
        }
    }

    public function calendar(Request $request)
    {
        abort_if_cannot('view_bookings');
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $barberId = $request->input('barber_id');

        $query = Booking::query()
            ->whereMonth('appointment_at', $month)
            ->whereYear('appointment_at', $year);

        if ($barberId) {
            $query->where('barber_id', $barberId);
        }

        $bookings = $query->get();
        $stats = [];
        foreach ($bookings as $b) {
            $dateStr = $b->appointment_at->format('Y-m-d');
            $stats[$dateStr] = ($stats[$dateStr] ?? 0) + 1;
        }

        return response()->json(['data' => (object)$stats]);
    }

    /**
     * Kohëzgjatja totale e një booking-u (mbështet shumë shërbime në notes).
     */
    private function bookingDuration(Booking $b, array $serviceDurationsByName = []): int
    {
        $duration = (int) ($b->service?->duration_minutes ?? 0);

        // If the booking was created from multiple services, calculate the
        // real duration from the service names stored in the notes. This is
        // done from an in-memory map so the schedule loop never fires a DB
        // query for every iteration.
        $notes = trim((string) ($b->notes ?? ''));
        if (str_starts_with($notes, 'Shërbimet: ')) {
            $names = array_values(array_filter(
                array_map('trim', explode('+', trim(substr($notes, strlen('Shërbimet: '))))),
                static fn ($name) => $name !== ''
            ));

            $sum = 0;
            foreach ($names as $name) {
                $sum += (int) ($serviceDurationsByName[$name] ?? 0);
            }

            if ($sum > 0) {
                $duration = $sum;
            }
        }

        // A booking must always consume positive time. Invalid/zero duration
        // must never be allowed to create a non-progressing cursor.
        return max(1, $duration > 0 ? $duration : 30);
    }

    /**
     * Normalizon një vlerë ore (string 24h "20:00:00", string 12h "08:00 PM",
     * objekt Carbon/DateTime nga casting i Eloquent-it, ose datetime i plotë)
     * në formatin e sigurt "H:i". Kthen null nëse s'mund të parsohet.
     */
    private function normalizeTimeStr($value): ?string
    {
        if (!$value) {
            return null;
        }
        try {
            return \Carbon\Carbon::parse((string) $value)->format('H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function daySchedule(Request $request)
    {
        abort_if_cannot('view_bookings');

        $dateStr = (string) $request->input('date', now()->format('Y-m-d'));
        $barberId = $request->input('barber_id') ?: \App\Models\Barber::value('id');
        $serviceId = $request->input('service_id');

        try {
            $date = \Carbon\Carbon::createFromFormat('Y-m-d', $dateStr)->startOfDay();
        } catch (\Throwable $e) {
            return response()->json([
                'daySlots' => [],
                'calendarStats' => [],
                'calendarMessage' => 'Data e zgjedhur nuk është valide.',
                'barbers' => [],
                'meta' => ['error' => 'invalid_date'],
            ], 422);
        }

        $dateStr = $date->format('Y-m-d');
        $dayName = $date->format('l');

        $barber = $barberId
            ? \App\Models\Barber::withoutGlobalScope('barber_shop_access')->find($barberId)
            : null;

        $workingHour = $barberId
            ? \App\Models\WorkingHour::withoutGlobalScope('barber_shop_access')
                ->where('barber_id', $barberId)
                ->where('day_of_week', $dayName)
                ->first()
            : null;

        if ($workingHour && $workingHour->is_closed) {
            return response()->json([
                'daySlots' => [],
                'calendarStats' => [],
                'calendarMessage' => 'Berberi është pushim sot.',
                'barbers' => [],
            ]);
        }

        $openTimeStr = $this->normalizeTimeStr($workingHour?->open_time) ?? '08:00';
        $closeTimeStr = $this->normalizeTimeStr($workingHour?->close_time) ?? '20:00';
        $lunchStartStr = $this->normalizeTimeStr($workingHour?->lunch_start);
        $lunchEndStr = $this->normalizeTimeStr($workingHour?->lunch_end);

        $openDt = \Carbon\Carbon::parse("{$dateStr} {$openTimeStr}:00");
        $closeDt = \Carbon\Carbon::parse("{$dateStr} {$closeTimeStr}:00");

        // Invalid working-hour configuration must not create an endless loop.
        if ($closeDt <= $openDt) {
            return response()->json([
                'daySlots' => [],
                'calendarStats' => [],
                'calendarMessage' => 'Orari i punës nuk është konfiguruar saktë.',
                'barbers' => [],
                'meta' => [
                    'open_time' => $openTimeStr,
                    'close_time' => $closeTimeStr,
                    'error' => 'close_before_or_equal_open',
                ],
            ], 422);
        }

        $lunchStartDt = null;
        $lunchEndDt = null;

        if ($lunchStartStr && $lunchEndStr) {
            $lunchStartDt = \Carbon\Carbon::parse("{$dateStr} {$lunchStartStr}:00");
            $lunchEndDt = \Carbon\Carbon::parse("{$dateStr} {$lunchEndStr}:00");

            // Ignore malformed lunch configuration rather than letting it
            // interfere with the slot cursor.
            if ($lunchEndDt <= $lunchStartDt) {
                $lunchStartDt = null;
                $lunchEndDt = null;
            }
        }

        $bookings = Booking::withoutGlobalScope('barber_shop_access')
            ->with(['customer', 'service', 'barber'])
            ->whereBetween('appointment_at', [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ])
            ->where('status', '!=', 'cancelled')
            ->when($barberId, fn ($q) => $q->where('barber_id', $barberId))
            ->orderBy('appointment_at')
            ->get();

        $barberShopId = $barber?->barber_shop_id;

        if (!$barberShopId && $request->input('barber_shop_id')) {
            $barberShopId = (int) $request->input('barber_shop_id');
        }

        if (!$barberShopId && $request->user()?->barber_shop_id) {
            $barberShopId = $request->user()->barber_shop_id;
        }

        $shop = $barberShopId ? \App\Models\BarberShop::find($barberShopId) : null;

        $activeServices = $barberShopId
            ? \App\Models\Service::withoutGlobalScope('barber_shop_access')
                ->where('barber_shop_id', $barberShopId)
                ->where('active', true)
                ->get(['id', 'name', 'duration_minutes'])
            : collect();

        // One in-memory lookup for multi-service booking durations.
        $serviceDurationsByName = $activeServices
            ->mapWithKeys(fn ($service) => [
                trim((string) $service->name) => max(1, (int) $service->duration_minutes),
            ])
            ->all();

        // Calculate minimum service time strictly isolated per shop_id
        $minServiceTime = $shop
            ? $shop->resolved_min_service_time
            : ($activeServices->where('duration_minutes', '>', 0)->min('duration_minutes') ?: 15);

        $stepMinutes = max(5, $minServiceTime);

        $reqDuration = $minServiceTime;
        if ($serviceId) {
            $serv = \App\Models\Service::withoutGlobalScope('barber_shop_access')->find($serviceId);
            if ($serv && (int) $serv->duration_minutes > 0) {
                $reqDuration = (int) $serv->duration_minutes;
            }
        }

        /*
         * IMPORTANT:
         * Pre-compute every booking's start/end once.
         *
         * The old implementation calculated bookingDuration() from inside
         * the while-loop and searched the collection repeatedly. Apart from
         * being expensive, that makes cursor progression fragile when
         * bookings overlap lunch or another booking.
         */
        $ignoreBookingId = $request->input('ignore_booking_id');
        $bookingEntries = $bookings
            ->filter(fn ($booking) => !$ignoreBookingId || (string)$booking->id !== (string)$ignoreBookingId)
            ->map(function ($booking) use ($serviceDurationsByName) {
                $start = $booking->appointment_at instanceof \Carbon\Carbon
                    ? $booking->appointment_at->copy()
                    : \Carbon\Carbon::parse($booking->appointment_at);

                $duration = $this->bookingDuration($booking, $serviceDurationsByName);
                $end = $start->copy()->addMinutes($duration);

                return [
                    'booking' => $booking,
                    'start' => $start,
                    'end' => $end,
                    'duration' => $duration,
                    'is_break' => false,
                ];
            });

        if ($lunchStartDt && $lunchEndDt) {
            $breakDuration = (int) floor(($lunchEndDt->getTimestamp() - $lunchStartDt->getTimestamp()) / 60);
            $breakBooking = new Booking();
            $breakBooking->id = 'break_' . $lunchStartDt->format('Hi');
            $breakBooking->barber_shop_id = $barberShopId;
            $breakBooking->barber_id = $barberId;
            $breakBooking->status = 'break';
            $breakBooking->notes = 'Orar pushimi i berberit';

            $bookingEntries->push([
                'booking' => $breakBooking,
                'start' => $lunchStartDt->copy(),
                'end' => $lunchEndDt->copy(),
                'duration' => $breakDuration,
                'is_break' => true,
            ]);
        }

        $bookingEntries = $bookingEntries->sortBy('start')->values();

        $slots = [];
        $slotTimes = [];

        $cursor = $openDt->copy();
        $iterations = 0;

        // Dynamic safety limit. Under normal conditions the loop needs far
        // fewer iterations; this is only a final circuit breaker.
        $workingMinutes = max(1, (int) floor(
            ($closeDt->getTimestamp() - $openDt->getTimestamp()) / 60
        ));
        $maxIterations = max(
            200,
            (int) ceil($workingMinutes / $stepMinutes) + ($bookingEntries->count() * 3) + 50
        );

        $stoppedBySafetyGuard = false;

        while ($cursor < $closeDt) {
            if (++$iterations > $maxIterations) {
                $stoppedBySafetyGuard = true;
                break;
            }

            $previousCursor = $cursor->copy();

            /*
             * Find the booking or break that currently occupies the cursor.
             */
            if ($cursor < $closeDt) {
                $activeBooking = $bookingEntries
                    ->filter(fn ($entry) =>
                        $entry['start'] <= $cursor &&
                        $entry['end'] > $cursor
                    )
                    ->sortByDesc(fn ($entry) => $entry['end']->getTimestamp())
                    ->first();

                if ($activeBooking) {
                    $booking = $activeBooking['booking'];
                    $bStart = $activeBooking['start'];
                    $bEnd = $activeBooking['end'];
                    $bDuration = $activeBooking['duration'];
                    $isBreak = $activeBooking['is_break'] ?? false;

                    if ($bStart->format('Y-m-d H:i') === $cursor->format('Y-m-d H:i')) {
                        $serviceName = $isBreak ? 'Orar Pushimi / Dreka' : ($booking->service?->name ?? 'Shërbim');
                        $customerName = $isBreak ? 'Pushim' : ($booking->customer?->name ?? 'Klient');
                        $status = $isBreak ? 'break' : $booking->status;

                        $paymentRecord = !$isBreak ? \App\Models\Payment::where('booking_id', $booking->id)->latest()->first() : null;
                        $paymentStatus = $paymentRecord?->status ?? $booking->payment_status ?? 'unpaid';

                        $matchedPrice = ($paymentRecord && (float) $paymentRecord->amount > 0)
                            ? $paymentRecord->amount
                            : (($booking->total_price && (float) $booking->total_price > 0)
                                ? $booking->total_price
                                : ($booking->service?->price ?? 0));

                        if (!$isBreak) {
                            $notes = trim((string) ($booking->notes ?? ''));
                            if (str_starts_with($notes, 'Shërbimet: ')) {
                                $names = array_values(array_filter(
                                    array_map(
                                        'trim',
                                        explode('+', trim(substr($notes, strlen('Shërbimet: '))))
                                    ),
                                    static fn ($name) => $name !== ''
                                ));

                                if (!empty($names)) {
                                    $serviceName = implode(' + ', $names);
                                }
                            }
                        }

                        $timeKey = $bStart->format('H:i');

                        if (!isset($slotTimes[$timeKey])) {
                            $smsMessages = !$isBreak ? \App\Models\MessageQueue::where('booking_id', $booking->id)
                                ->get()
                                ->map(function ($queue) {
                                    $type = $queue->resolved_template_type;
                                    $typeLabel = $type === 'reminder' ? 'Rikujtesë SMS' : ($type === 'welcome' ? 'Mirëseardhje SMS' : 'Konfirmim SMS');
                                    return [
                                        'id' => $queue->id,
                                        'type' => $type,
                                        'type_label' => $typeLabel,
                                        'message_content' => $queue->message_content,
                                        'status' => $queue->status,
                                        'scheduled_at' => $queue->scheduled_at?->toIso8601String(),
                                        'created_at' => $queue->created_at?->toIso8601String(),
                                        'updated_at' => $queue->updated_at?->toIso8601String(),
                                    ];
                                })->values()->toArray() : [];

                            $slots[] = [
                                'time' => $timeKey,
                                'is_free' => false,
                                'booking' => [
                                    'id' => $booking->id,
                                    'barber_shop_id' => $booking->barber_shop_id,
                                    'barber_id' => $booking->barber_id,
                                    'service_id' => $booking->service_id ?? null,
                                    'customer_id' => $booking->customer_id ?? null,
                                    'appointment_at' => $booking->appointment_at instanceof \Carbon\Carbon ? $booking->appointment_at->format('Y-m-d H:i:s') : ($booking->appointment_at ?? $bStart->format('Y-m-d H:i:s')),
                                    'customer_name' => $customerName,
                                    'service_name' => $serviceName,
                                    'barber_name' => $barber?->name ?? 'Berber',
                                    'status' => $status,
                                    'payment_status' => $isBreak ? 'na' : $paymentStatus,
                                    'total_price' => $isBreak ? 0 : $matchedPrice,
                                    'notes' => $booking->notes,
                                    'duration_minutes' => $bDuration,
                                    'sms_messages' => $smsMessages,
                                ],
                            ];

                            $slotTimes[$timeKey] = true;
                        }
                    }

                    // Check if any break entries fall inside or intersect this booking's interval [bStart, bEnd)
                    $internalBreaks = $bookingEntries->filter(fn ($entry) =>
                        ($entry['is_break'] ?? false) &&
                        $entry['start'] >= $bStart &&
                        $entry['start'] < $bEnd
                    );

                    foreach ($internalBreaks as $breakEntry) {
                        $breakTimeKey = $breakEntry['start']->format('H:i');
                        if (!isset($slotTimes[$breakTimeKey])) {
                            $slots[] = [
                                'time' => $breakTimeKey,
                                'is_free' => false,
                                'booking' => [
                                    'id' => $breakEntry['booking']->id,
                                    'barber_shop_id' => $barberShopId,
                                    'barber_id' => $barberId,
                                    'service_id' => null,
                                    'customer_id' => null,
                                    'appointment_at' => $breakEntry['start']->format('Y-m-d H:i:s'),
                                    'customer_name' => 'Pushim',
                                    'service_name' => 'Orar Pushimi / Dreka',
                                    'barber_name' => $barber?->name ?? 'Berber',
                                    'status' => 'break',
                                    'payment_status' => 'na',
                                    'total_price' => 0,
                                    'notes' => 'Orar pushimi i berberit',
                                    'duration_minutes' => $breakEntry['duration'],
                                ],
                            ];
                            $slotTimes[$breakTimeKey] = true;
                        }
                    }

                    $cursor = $bEnd->copy();
                    continue;
                }
            }

            if ($cursor >= $closeDt) {
                break;
            }

            /*
             * 3. No booking currently occupies the cursor.
             * Find the next booking and/or lunch as the end of the free
             * interval. Then create a 15-minute grid slot.
             */
            $nextBooking = $bookingEntries
                ->first(fn ($entry) => $entry['start'] > $cursor);

            $slotStart = $cursor->copy();
            $timeStr = $slotStart->format('H:i');

            if (isset($slotTimes[$timeStr])) {
                $cursor = $slotStart->copy()->addMinutes($stepMinutes);
            } else {
                $limit = $closeDt->copy();

                if (
                    $lunchStartDt &&
                    $lunchStartDt > $slotStart &&
                    $lunchStartDt < $limit
                ) {
                    $limit = $lunchStartDt->copy();
                }

                if ($nextBooking && $nextBooking['start'] < $limit) {
                    $limit = $nextBooking['start']->copy();
                }

                $available = (int) floor(
                    ($limit->getTimestamp() - $slotStart->getTimestamp()) / 60
                );

                if ($available >= $stepMinutes && $limit > $slotStart) {
                    $fitsRequested = $available >= $reqDuration;

                    $slot = [
                        'time' => $timeStr,
                        'is_free' => true,
                        'booking' => null,
                        'available_minutes' => $available,
                        'is_partial' => !$fitsRequested,
                    ];

                    if (!$fitsRequested) {
                        $allowed = $activeServices
                            ->filter(fn ($service) =>
                                (int) $service->duration_minutes > 0 &&
                                (int) $service->duration_minutes <= $available
                            )
                            ->values();

                        $slot['allowed_services'] = $allowed->map(fn ($service) => [
                            'id' => $service->id,
                            'name' => $service->name,
                            'duration_minutes' => (int) $service->duration_minutes,
                        ])->all();

                        $allowedNames = $allowed->pluck('name')->implode(', ');

                        $slot['message'] = 'Në këtë orar mund të bëni vetëm: '
                            . $allowedNames
                            . " (maksimumi {$available} min). Për më shumë kontaktoni berberin.";
                    }

                    $slots[] = $slot;
                    $slotTimes[$timeStr] = true;

                    // Normal grid progression.
                    $cursor = $slotStart->copy()->addMinutes($stepMinutes);
                } else {
                    /*
                     * There is less than one grid slot before the next
                     * boundary. Jump directly to that boundary.
                     */
                    $cursor = $limit > $slotStart
                        ? $limit->copy()
                        : $slotStart->copy()->addMinutes($stepMinutes);
                }
            }

            /*
             * GLOBAL PROGRESS GUARANTEE.
             *
             * Every branch above must move the cursor. This final watchdog
             * makes that invariant explicit and prevents any future change
             * from bringing back the 1000-iteration freeze.
             */
            if ($cursor <= $previousCursor) {
                $cursor = $previousCursor->copy()->addMinutes($stepMinutes);
            }
        }

        return response()->json([
            'daySlots' => $slots,
            'calendarStats' => [],
            'calendarMessage' => empty($slots)
                ? 'Nuk ka slot-e të lira për këtë ditë.'
                : null,
            'barbers' => [],
            'meta' => [
                'day_of_week_kerkuar' => $dayName,
                'working_hour_gjetur' => $workingHour ? true : false,
                'raw_open_time' => $workingHour?->open_time,
                'raw_close_time' => $workingHour?->close_time,
                'raw_lunch_start' => $workingHour?->lunch_start,
                'raw_lunch_end' => $workingHour?->lunch_end,
                'open_time' => $openTimeStr,
                'close_time' => $closeTimeStr,
                'lunch_start' => $lunchStartStr,
                'lunch_end' => $lunchEndStr,
                'debug_iterations' => $iterations,
                'debug_max_iterations' => $maxIterations,
                'debug_cursor_final' => $cursor->format('Y-m-d H:i'),
                'debug_close_dt' => $closeDt->format('Y-m-d H:i'),
                'debug_u_ndal_nga_max_iterations' => $stoppedBySafetyGuard,
                'debug_bookings_te_dites' => $bookingEntries->map(fn ($entry) => [
                    'id' => $entry['booking']->id,
                    'appointment_at' => $entry['start']->format('Y-m-d H:i:s'),
                    'end_at' => $entry['end']->format('Y-m-d H:i:s'),
                    'status' => $entry['booking']->status,
                    'duration_minutes' => $entry['duration'],
                    'service_id' => $entry['booking']->service_id,
                    'notes' => $entry['booking']->notes,
                ])->values(),
            ],
        ]);
    }

    private function prepareData(Request $request): array
    {
        $data = $request->all();

        foreach (array (
) as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $decoded = json_decode($data[$field], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data[$field] = $decoded;
                }
            }
        }

        foreach (array (
) as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/bookings');
            }
        }

        return $data;
    }
}
