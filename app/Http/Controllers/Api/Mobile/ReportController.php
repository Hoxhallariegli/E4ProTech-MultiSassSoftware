<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\ReportResource;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Domain\Report\DTOs\ReportDTO;
use App\Domain\Report\Actions\CreateReportAction;
use App\Domain\Report\Actions\UpdateReportAction;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $user = $request->user();
        $shopId = $request->input('barber_shop_id', $user?->barber_shop_id);
        $barberId = $request->input('barber_id');
        $period = strtolower((string) $request->input('period_type', 'daily'));
        $dateStr = (string) $request->input('report_date', date('Y-m-d'));

        $query = \App\Models\Booking::withoutGlobalScope('barber_shop_access');

        if ($shopId) {
            $query->where('barber_shop_id', $shopId);
        }

        if ($barberId) {
            $query->where('barber_id', $barberId);
        }

        try {
            $baseDate = Carbon::parse($dateStr);
        } catch (\Throwable $e) {
            $baseDate = Carbon::today();
        }

        $startDate = $baseDate->copy()->startOfDay();
        $endDate = $baseDate->copy()->endOfDay();

        if ($period === 'weekly') {
            $startDate = $baseDate->copy()->startOfWeek();
            $endDate = $baseDate->copy()->endOfWeek();
        } elseif ($period === 'monthly') {
            $startDate = $baseDate->copy()->startOfMonth();
            $endDate = $baseDate->copy()->endOfMonth();
        }

        $periodQuery = (clone $query)->whereBetween('appointment_at', [$startDate, $endDate]);

        // Direct SQL Aggregations (Ultra-fast 1ms, zero memory overhead!)
        $totalBookings = (clone $periodQuery)->count();

        $calculatedRevenue = (float) (clone $periodQuery)
            ->where(function ($q) {
                $q->where('status', 'completed')
                  ->orWhere('payment_status', 'paid');
            })
            ->sum('total_price');

        $potentialEarnings = (float) (clone $periodQuery)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where(function ($q) {
                $q->whereNull('payment_status')
                  ->orWhere('payment_status', '!=', 'paid');
            })
            ->sum('total_price');

        $totalCompleted = (clone $periodQuery)->where('status', 'completed')->count();
        $totalCancelled = (clone $periodQuery)->where('status', 'cancelled')->count();

        $barberName = $barberId ? (\App\Models\Barber::find($barberId)?->name ?? 'Stafi') : 'Gjithë Stafi';

        $metrics = [
            'barber_shop_id' => $shopId,
            'barber_id' => $barberId ? (int)$barberId : null,
            'period_type' => $period,
            'report_date' => $dateStr,
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'end_date' => $endDate->format('Y-m-d H:i:s'),
            'total_bookings' => $totalBookings,
            'total_revenue' => (float) $calculatedRevenue,
            'potential_earnings' => (float) $potentialEarnings,
            'total_completed' => $totalCompleted,
            'total_pending' => max(0, $totalBookings - $totalCompleted - $totalCancelled),
            'total_cancelled' => $totalCancelled,
            'notes' => "Kalkuluar automatikisht nga baza e të dhënave për {$barberName}.",
        ];

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    public function index(Request $request)
    {
        abort_if_cannot('view_reports');

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = Report::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = Report::query()->with([
            'barber',
            'barberShop',
        ]);

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
                foreach (['notes'] as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        foreach ($request->all() as $key => $value) {
            if ($value === null || $value === '' || !str_ends_with($key, '_id')) {
                continue;
            }
            if (in_array($key, array (), true)) {
                continue;
            }
            if (in_array($key, array_keys(Report::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return ReportResource::collection($items);
    }

    public function show($id)
    {
        abort_if_cannot('view_reports');
        $item = Report::with([
            'barber',
            'barberShop',
        ])->findOrFail($id);
        return new ReportResource($item);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_reports');
        $data = $this->prepareData($request);

        $validated = validator($data, Report::rules())->validate();
        $item = app(\App\Domain\Report\Actions\CreateReportAction::class)->execute(\App\Domain\Report\DTOs\ReportDTO::fromArray($validated));
        return (new ReportResource($item->loadMissing([
            'barber',
            'barberShop',
        ])))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_reports');
        $item = Report::findOrFail($id);
        $data = $this->prepareData($request);
        $validated = validator($data, Report::rules($id))->validate();
        $item = app(\App\Domain\Report\Actions\UpdateReportAction::class)->execute($item, \App\Domain\Report\DTOs\ReportDTO::fromArray($validated));
        return new ReportResource($item->loadMissing([
            'barber',
            'barberShop',
        ]));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_reports');

        try {
            $item = Report::findOrFail($id);
            app(\App\Domain\Report\Actions\DeleteReportAction::class)->execute($item);
            return response()->json(['success' => true, 'message' => 'Report deleted.']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Delete Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Record is referenced by other data and cannot be deleted.',
            ], 409);
        }
    }

    private function prepareData(Request $request): array
    {
        $data = $request->all();
        return $data;
    }
}
