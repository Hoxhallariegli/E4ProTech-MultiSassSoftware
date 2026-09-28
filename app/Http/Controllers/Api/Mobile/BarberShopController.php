<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\BarberShopResource;
use \App\Models\BarberShop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Domain\BarberShop\DTOs\BarberShopDTO;
use App\Domain\BarberShop\Actions\CreateBarberShopAction;
use App\Domain\BarberShop\Actions\UpdateBarberShopAction;

class BarberShopController extends Controller
{
    public function index(Request $request)
    {
        abort_if_cannot('view_barber_shops');

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = BarberShop::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = BarberShop::query()->with(array (
  0 => 'owner',
));

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
                foreach (array (
  0 => 'name',
  1 => 'app_name',
  2 => 'slug',
  3 => 'primary_color',
  4 => 'secondary_color',
  5 => 'timezone',
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
            if (in_array($key, array_keys(BarberShop::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return BarberShopResource::collection($items);
    }

    public function show($id)
    {
        abort_if_cannot('view_barber_shops');
        $item = BarberShop::with(array (
  0 => 'owner',
))->findOrFail($id);
        return new BarberShopResource($item);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_barber_shops');
                $data = $this->prepareData($request);
        
        $validated = validator($data, BarberShop::rules())->validate();
        $item = app(\App\Domain\BarberShop\Actions\CreateBarberShopAction::class)->execute(\App\Domain\BarberShop\DTOs\BarberShopDTO::fromArray($validated));
        return (new BarberShopResource($item->loadMissing(array (
  0 => 'owner',
))))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_barber_shops');
                $item = BarberShop::findOrFail($id);
        $data = $this->prepareData($request);
        $validated = validator($data, BarberShop::rules($id))->validate();
        $item = app(\App\Domain\BarberShop\Actions\UpdateBarberShopAction::class)->execute($item, \App\Domain\BarberShop\DTOs\BarberShopDTO::fromArray($validated));
        return new BarberShopResource($item->loadMissing(array (
  0 => 'owner',
)));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_barber_shops');

        try {
            $item = BarberShop::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'BarberShop deleted.']);
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
  0 => 'logo',
  1 => 'banner',
) as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/barber-shops');
            }
        }

        return $data;
    }
}