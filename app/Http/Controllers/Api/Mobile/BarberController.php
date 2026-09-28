<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\BarberResource;
use \App\Models\Barber;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Domain\Barber\DTOs\BarberDTO;
use App\Domain\Barber\Actions\CreateBarberAction;
use App\Domain\Barber\Actions\UpdateBarberAction;

class BarberController extends Controller
{
    public function index(Request $request)
    {
        abort_if_cannot('view_barbers');

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = Barber::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = Barber::query()->with(array (
  0 => 'barberShop',
  1 => 'user',
));

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
                foreach (array (
  0 => 'name',
  1 => 'phone',
  2 => 'bio',
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
            if (in_array($key, array_keys(Barber::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return BarberResource::collection($items);
    }

    public function show($id)
    {
        abort_if_cannot('view_barbers');
        $item = Barber::with(array (
  0 => 'barberShop',
  1 => 'user',
))->findOrFail($id);
        return new BarberResource($item);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_barbers');
                $data = $this->prepareData($request);
        
        $shopId = $request->input('barber_shop_id', $request->user()->barber_shop_id);
        $shop = \App\Models\BarberShop::find($shopId);
        if ($shop && !$request->user()->is_global_admin) {
            if (!app(\App\Services\SubscriptionService::class)->canAddBarber($shop)) {
                return response()->json(['message' => __('Limit reached for this plan.')], 403);
            }
        }

        $validated = validator($data, Barber::rules())->validate();
        $item = app(\App\Domain\Barber\Actions\CreateBarberAction::class)->execute(\App\Domain\Barber\DTOs\BarberDTO::fromArray($validated));
        return (new BarberResource($item->loadMissing(array (
  0 => 'barberShop',
  1 => 'user',
))))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_barbers');
                $item = Barber::findOrFail($id);
        $data = $this->prepareData($request);
        $validated = validator($data, Barber::rules($id))->validate();
        $item = app(\App\Domain\Barber\Actions\UpdateBarberAction::class)->execute($item, \App\Domain\Barber\DTOs\BarberDTO::fromArray($validated));
        return new BarberResource($item->loadMissing(array (
  0 => 'barberShop',
  1 => 'user',
)));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_barbers');

        try {
            $item = Barber::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'Barber deleted.']);
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
  0 => 'photo',
) as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/barbers');
            }
        }

        return $data;
    }
}