<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\EventSettingResource;
use \App\Models\EventSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Domain\EventSetting\DTOs\EventSettingDTO;
use App\Domain\EventSetting\Actions\CreateEventSettingAction;
use App\Domain\EventSetting\Actions\UpdateEventSettingAction;

class EventSettingController extends Controller
{
    public function index(Request $request)
    {
        abort_if_cannot('view_event_settings');

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = EventSetting::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = EventSetting::query()->with(array (
  0 => 'barberShop',
  1 => 'realtimeEvent',
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
            if (in_array($key, array_keys(EventSetting::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return EventSettingResource::collection($items);
    }

    public function show($id)
    {
        abort_if_cannot('view_event_settings');
        $item = EventSetting::with(array (
  0 => 'barberShop',
  1 => 'realtimeEvent',
))->findOrFail($id);
        return new EventSettingResource($item);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_event_settings');
                $data = $this->prepareData($request);
        
        $validated = validator($data, EventSetting::rules())->validate();
        $item = app(\App\Domain\EventSetting\Actions\CreateEventSettingAction::class)->execute(\App\Domain\EventSetting\DTOs\EventSettingDTO::fromArray($validated));
        return (new EventSettingResource($item->loadMissing(array (
  0 => 'barberShop',
  1 => 'realtimeEvent',
))))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_event_settings');
                $item = EventSetting::findOrFail($id);
        $data = $this->prepareData($request);
        $validated = validator($data, EventSetting::rules($id))->validate();
        $item = app(\App\Domain\EventSetting\Actions\UpdateEventSettingAction::class)->execute($item, \App\Domain\EventSetting\DTOs\EventSettingDTO::fromArray($validated));
        return new EventSettingResource($item->loadMissing(array (
  0 => 'barberShop',
  1 => 'realtimeEvent',
)));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_event_settings');

        try {
            $item = EventSetting::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'EventSetting deleted.']);
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
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/event-settings');
            }
        }

        return $data;
    }
}