<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\DeviceTokenResource;
use \App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Domain\DeviceToken\DTOs\DeviceTokenDTO;
use App\Domain\DeviceToken\Actions\CreateDeviceTokenAction;
use App\Domain\DeviceToken\Actions\UpdateDeviceTokenAction;

class DeviceTokenController extends Controller
{
    public function saveWebToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'platform' => 'nullable|string',
            'device_name' => 'nullable|string',
        ]);

        $user = $request->user() ?: auth()->user();
        $fcmToken = $request->input('fcm_token');
        $platform = $request->input('platform', 'android');
        $deviceName = $request->input('device_name', 'Mobile Device');

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $shopId = $user->barber_shop_id ?: \App\Models\BarberShop::value('id');

        if (!$shopId) {
            return response()->json(['message' => 'Llogaria juaj nuk ka dyqan aktiv.'], 422);
        }

        $deviceToken = DeviceToken::updateOrCreate(
            [
                'barber_shop_id' => $shopId,
                'user_id' => $user->id,
                'fcm_token' => $fcmToken,
            ],
            [
                'platform' => in_array($platform, ['android', 'ios', 'web']) ? $platform : 'android',
                'is_sms_gateway' => false,
                'device_name' => $deviceName,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Token-i i pajisjes u regjistrua me sukses te baza e të dhënave!',
            'data' => $deviceToken,
        ]);
    }

    public function index(Request $request)
    {
        abort_if_cannot('view_device_tokens');

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = DeviceToken::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = DeviceToken::query()->with([
            'barberShop',
            'user',
        ]);

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
                foreach (['fcm_token', 'device_name'] as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        foreach ($request->all() as $key => $value) {
            if ($value === null || $value === '' || !str_ends_with($key, '_id')) {
                continue;
            }
            if (in_array($key, [], true)) {
                continue;
            }
            if (in_array($key, array_keys(DeviceToken::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return DeviceTokenResource::collection($items);
    }

    public function show($id)
    {
        abort_if_cannot('view_device_tokens');
        $item = DeviceToken::with([
            'barberShop',
            'user',
        ])->findOrFail($id);
        return new DeviceTokenResource($item);
    }

    public function setPrimaryGateway(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'is_sms_gateway' => 'required|boolean',
            'device_name' => 'nullable|string',
        ]);

        $user = $request->user();
        $shopId = $user->barber_shop_id;
        $fcmToken = $request->input('fcm_token');
        $isSmsGateway = $request->boolean('is_sms_gateway');

        if (!$shopId) {
            return response()->json(['message' => 'Llogaria juaj nuk ka dyqan aktiv.'], 422);
        }

        if ($isSmsGateway) {
            // Unset all other devices for this shop to ensure only 1 primary SMS gateway device
            DeviceToken::where('barber_shop_id', $shopId)->update(['is_sms_gateway' => false]);

            $deviceToken = DeviceToken::updateOrCreate(
                [
                    'barber_shop_id' => $shopId,
                    'user_id' => $user->id,
                    'fcm_token' => $fcmToken,
                ],
                [
                    'platform' => $request->input('platform', 'android'),
                    'is_sms_gateway' => true,
                    'device_name' => $request->input('device_name', 'Android Device'),
                    'last_used_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'is_sms_gateway' => true,
                'message' => 'Kjo pajisje u caktua si SMS Gateway kryesor i sallonit.',
            ]);
        } else {
            // When turning OFF, delete the device token record from the DB
            DeviceToken::where('barber_shop_id', $shopId)
                ->where('fcm_token', $fcmToken)
                ->delete();

            return response()->json([
                'success' => true,
                'is_sms_gateway' => false,
                'message' => 'Pajisja u çaktivizua dhe u hoq nga lista.',
            ]);
        }
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_device_tokens');
        $data = $this->prepareData($request);

        $validated = validator($data, DeviceToken::rules())->validate();
        $item = app(\App\Domain\DeviceToken\Actions\CreateDeviceTokenAction::class)->execute(\App\Domain\DeviceToken\DTOs\DeviceTokenDTO::fromArray($validated));
        return (new DeviceTokenResource($item->loadMissing([
            'barberShop',
            'user',
        ])))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_device_tokens');
        $item = DeviceToken::findOrFail($id);
        $data = $this->prepareData($request);
        $validated = validator($data, DeviceToken::rules($id))->validate();
        $item = app(\App\Domain\DeviceToken\Actions\UpdateDeviceTokenAction::class)->execute($item, \App\Domain\DeviceToken\DTOs\DeviceTokenDTO::fromArray($validated));
        return new DeviceTokenResource($item->loadMissing([
            'barberShop',
            'user',
        ]));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_device_tokens');

        try {
            $item = DeviceToken::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'DeviceToken deleted.']);
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
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/device-tokens');
            }
        }

        return $data;
    }
}
