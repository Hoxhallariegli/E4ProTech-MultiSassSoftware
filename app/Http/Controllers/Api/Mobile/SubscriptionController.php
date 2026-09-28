<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\SubscriptionResource;
use \App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Domain\Subscription\DTOs\SubscriptionDTO;
use App\Domain\Subscription\Actions\CreateSubscriptionAction;
use App\Domain\Subscription\Actions\UpdateSubscriptionAction;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        abort_if_cannot('view_subscriptions');

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = Subscription::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = Subscription::query()->with(array (
  0 => 'barberShop',
  1 => 'plan',
));

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
                foreach (array (
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
            if (in_array($key, array_keys(Subscription::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return SubscriptionResource::collection($items);
    }

    public function show($id)
    {
        abort_if_cannot('view_subscriptions');
        $item = Subscription::with(array (
  0 => 'barberShop',
  1 => 'plan',
))->findOrFail($id);
        return new SubscriptionResource($item);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_subscriptions');
                $data = $this->prepareData($request);
        
        $validated = validator($data, Subscription::rules())->validate();
        $item = app(\App\Domain\Subscription\Actions\CreateSubscriptionAction::class)->execute(\App\Domain\Subscription\DTOs\SubscriptionDTO::fromArray($validated));
        return (new SubscriptionResource($item->loadMissing(array (
  0 => 'barberShop',
  1 => 'plan',
))))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_subscriptions');
                $item = Subscription::findOrFail($id);
        $data = $this->prepareData($request);
        $validated = validator($data, Subscription::rules($id))->validate();
        $item = app(\App\Domain\Subscription\Actions\UpdateSubscriptionAction::class)->execute($item, \App\Domain\Subscription\DTOs\SubscriptionDTO::fromArray($validated));
        return new SubscriptionResource($item->loadMissing(array (
  0 => 'barberShop',
  1 => 'plan',
)));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_subscriptions');

        try {
            $item = Subscription::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'Subscription deleted.']);
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
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/subscriptions');
            }
        }

        return $data;
    }
}