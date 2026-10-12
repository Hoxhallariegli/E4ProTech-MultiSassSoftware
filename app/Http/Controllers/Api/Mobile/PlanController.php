<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\PlanResource;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Domain\Plan\DTOs\PlanDTO;
use App\Domain\Plan\Actions\CreatePlanAction;
use App\Domain\Plan\Actions\UpdatePlanAction;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        // Plans are readable by all authenticated users to select/view subscription options
        $perPage = min(max((int) $request->integer('per_page', 50), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'asc'));
        $allowedSorts = Plan::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $query = Plan::query();

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
                foreach (['name'] as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        foreach ($request->all() as $key => $value) {
            if ($value === null || $value === '' || !str_ends_with($key, '_id')) {
                continue;
            }
            if (in_array($key, array_keys(Plan::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return PlanResource::collection($items);
    }

    public function show($id)
    {
        // Plans are readable by all authenticated users
        $item = Plan::findOrFail($id);
        return new PlanResource($item);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_plans');
        $data = $this->prepareData($request);

        $validated = validator($data, Plan::rules())->validate();
        $item = app(CreatePlanAction::class)->execute(PlanDTO::fromArray($validated));
        return (new PlanResource($item))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_plans');
        $item = Plan::findOrFail($id);
        $data = $this->prepareData($request);
        $validated = validator($data, Plan::rules($id))->validate();
        $item = app(UpdatePlanAction::class)->execute($item, PlanDTO::fromArray($validated));
        return new PlanResource($item);
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_plans');

        try {
            $item = Plan::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'Plan deleted.']);
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
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/plans');
            }
        }

        return $data;
    }
}
