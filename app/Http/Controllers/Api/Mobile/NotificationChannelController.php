<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\NotificationChannelResource;
use \App\Models\NotificationChannel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Domain\NotificationChannel\DTOs\NotificationChannelDTO;
use App\Domain\NotificationChannel\Actions\CreateNotificationChannelAction;
use App\Domain\NotificationChannel\Actions\UpdateNotificationChannelAction;

class NotificationChannelController extends Controller
{
    public function index(Request $request)
    {
        abort_if_cannot('view_notification_channels');

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = NotificationChannel::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = NotificationChannel::query()->with(array (
  0 => 'barberShop',
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
            if (in_array($key, array_keys(NotificationChannel::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return NotificationChannelResource::collection($items);
    }

    public function show($id)
    {
        abort_if_cannot('view_notification_channels');
        $item = NotificationChannel::with(array (
  0 => 'barberShop',
))->findOrFail($id);
        return new NotificationChannelResource($item);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_notification_channels');
                $data = $this->prepareData($request);
        
        $validated = validator($data, NotificationChannel::rules())->validate();
        $item = app(\App\Domain\NotificationChannel\Actions\CreateNotificationChannelAction::class)->execute(\App\Domain\NotificationChannel\DTOs\NotificationChannelDTO::fromArray($validated));
        return (new NotificationChannelResource($item->loadMissing(array (
  0 => 'barberShop',
))))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_notification_channels');
                $item = NotificationChannel::findOrFail($id);
        $data = $this->prepareData($request);
        $validated = validator($data, NotificationChannel::rules($id))->validate();
        $item = app(\App\Domain\NotificationChannel\Actions\UpdateNotificationChannelAction::class)->execute($item, \App\Domain\NotificationChannel\DTOs\NotificationChannelDTO::fromArray($validated));
        return new NotificationChannelResource($item->loadMissing(array (
  0 => 'barberShop',
)));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_notification_channels');

        try {
            $item = NotificationChannel::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'NotificationChannel deleted.']);
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
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/notification-channels');
            }
        }

        return $data;
    }
}