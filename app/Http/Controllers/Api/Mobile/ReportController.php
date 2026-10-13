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

class ReportController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $shopId = $request->input('barber_shop_id', $request->user()?->barber_shop_id);
        $barberId = $request->input('barber_id');
        $period = $request->input('period_type', 'daily');
        $dateStr = $request->input('report_date', date('Y-m-d'));

        $query = \App\Models\Booking::where('barber_shop_id', $shopId);

        if ($barberId) {
            $query->where('barber_id', $barberId);
        }

        $startDate = \Carbon\Carbon::parse($dateStr)->startOfDay();
        $endDate = \Carbon\Carbon::parse($dateStr)->endOfDay();

        if ($period === 'weekly') {
            $startDate = \Carbon\Carbon::parse($dateStr)->startOfWeek();
            $endDate = \Carbon\Carbon::parse($dateStr)->endOfWeek();
        } elseif ($period === 'monthly') {
            $startDate = \Carbon\Carbon::parse($dateStr)->startOfMonth();
            $endDate = \Carbon\Carbon::parse($dateStr)->endOfMonth();
        }

        $bookings = $query->whereBetween('appointment_at', [$startDate, $endDate])->get();

        $barberName = $barberId ? (\App\Models\Barber::find($barberId)?->name ?? 'Stafi') : 'Gjithë Stafi';

        $calculatedRevenue = $bookings->filter(function($b) {
            return $b->status !== 'cancelled' && (
                in_array($b->status, ['completed', 'paid', 'confirmed'], true)
                || ($b->payment_status ?? '') === 'paid'
                || ($b->payment?->status ?? '') === 'paid'
            );
        })->sum('total_price');

        $metrics = [
            'barber_shop_id' => $shopId,
            'barber_id' => $barberId ? (int)$barberId : null,
            'period_type' => $period,
            'report_date' => $dateStr,
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'end_date' => $endDate->format('Y-m-d H:i:s'),
            'total_bookings' => $bookings->count(),
            'total_revenue' => (float) $calculatedRevenue,
            'total_completed' => $bookings->where('status', 'completed')->count(),
            'total_cancelled' => $bookings->where('status', 'cancelled')->count(),
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
            if (in_array($key, array (
), true)) {
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
            $item->delete();
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

        foreach ([
        ] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $decoded = json_decode($data[$field], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data[$field] = $decoded;
                }
            }
        }

        foreach ([
        ] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/reports');
            }
        }

        return $data;
    }
}
