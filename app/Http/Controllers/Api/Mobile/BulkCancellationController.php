<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\BulkCancellationResource;
use \App\Models\BulkCancellation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Domain\BulkCancellation\DTOs\BulkCancellationDTO;
use App\Domain\BulkCancellation\Actions\CreateBulkCancellationAction;
use App\Domain\BulkCancellation\Actions\UpdateBulkCancellationAction;

class BulkCancellationController extends Controller
{
    public function index(Request $request)
    {
        abort_if_cannot('view_bulk_cancellations');

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = BulkCancellation::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = BulkCancellation::query()->with(array (
  0 => 'barberShop',
  1 => 'barber',
));

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
                foreach (array (
  0 => 'reason',
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
            if (in_array($key, array_keys(BulkCancellation::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return BulkCancellationResource::collection($items);
    }

    public function show($id)
    {
        abort_if_cannot('view_bulk_cancellations');
        $item = BulkCancellation::with(array (
  0 => 'barberShop',
  1 => 'barber',
))->findOrFail($id);
        return new BulkCancellationResource($item);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_bulk_cancellations');
                $data = $this->prepareData($request);
        
        $validated = validator($data, BulkCancellation::rules())->validate();
        $item = app(\App\Domain\BulkCancellation\Actions\CreateBulkCancellationAction::class)->execute(\App\Domain\BulkCancellation\DTOs\BulkCancellationDTO::fromArray($validated));
        return (new BulkCancellationResource($item->loadMissing(array (
  0 => 'barberShop',
  1 => 'barber',
))))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_bulk_cancellations');
                $item = BulkCancellation::findOrFail($id);
        $data = $this->prepareData($request);
        $validated = validator($data, BulkCancellation::rules($id))->validate();
        $item = app(\App\Domain\BulkCancellation\Actions\UpdateBulkCancellationAction::class)->execute($item, \App\Domain\BulkCancellation\DTOs\BulkCancellationDTO::fromArray($validated));
        return new BulkCancellationResource($item->loadMissing(array (
  0 => 'barberShop',
  1 => 'barber',
)));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_bulk_cancellations');

        try {
            $item = BulkCancellation::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'BulkCancellation deleted.']);
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
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/bulk-cancellations');
            }
        }

        return $data;
    }
}