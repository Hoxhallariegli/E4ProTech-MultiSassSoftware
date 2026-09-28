<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\ServiceResource;
use \App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Domain\Service\DTOs\ServiceDTO;
use App\Domain\Service\Actions\CreateServiceAction;
use App\Domain\Service\Actions\UpdateServiceAction;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        abort_if_cannot('view_services');

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = Service::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = Service::query()->with(array (
  0 => 'barberShop',
));

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
                foreach (array (
  0 => 'name',
  1 => 'description',
  2 => 'category',
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
            if (in_array($key, array_keys(Service::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return ServiceResource::collection($items);
    }

    public function show($id)
    {
        abort_if_cannot('view_services');
        $item = Service::with(array (
  0 => 'barberShop',
))->findOrFail($id);
        return new ServiceResource($item);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_services');
        $data = $this->prepareData($request);

        if (empty($data['barber_shop_id']) && $request->user()?->barber_shop_id) {
            $data['barber_shop_id'] = $request->user()->barber_shop_id;
        }

        $shopId = $data['barber_shop_id'] ?? $request->user()?->barber_shop_id;
        $shop = \App\Models\BarberShop::find($shopId);
        if ($shop && !$request->user()->is_global_admin) {
            if (!app(\App\Services\SubscriptionService::class)->canAddService($shop)) {
                return response()->json(['message' => __('Limit reached for this plan.')], 403);
            }
        }

        $validated = validator($data, Service::rules())->validate();
        $item = app(\App\Domain\Service\Actions\CreateServiceAction::class)->execute(\App\Domain\Service\DTOs\ServiceDTO::fromArray($validated));
        return (new ServiceResource($item->loadMissing(array (
  0 => 'barberShop',
))))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_services');
        $item = Service::findOrFail($id);
        $data = $this->prepareData($request);

        if (empty($data['barber_shop_id']) && $request->user()?->barber_shop_id) {
            $data['barber_shop_id'] = $request->user()->barber_shop_id;
        }

        $validated = validator($data, Service::rules($id))->validate();
        $item = app(\App\Domain\Service\Actions\UpdateServiceAction::class)->execute($item, \App\Domain\Service\DTOs\ServiceDTO::fromArray($validated));
        return new ServiceResource($item->loadMissing(array (
  0 => 'barberShop',
)));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_services');

        try {
            $item = Service::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'Service deleted.']);
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
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/services');
            }
        }

        return $data;
    }
}
