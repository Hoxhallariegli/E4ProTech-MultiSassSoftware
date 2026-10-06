<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\DeviceTokenResource;
use App\Models\DeviceToken;
use App\Models\BarberShop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Domain\DeviceToken\DTOs\DeviceTokenDTO;
use App\Domain\DeviceToken\Actions\CreateDeviceTokenAction;
use App\Domain\DeviceToken\Actions\UpdateDeviceTokenAction;

class DeviceTokenController extends Controller
{
    public function saveWebToken(Request $request): JsonResponse
    {
        Log::info('🔥 RAW REQUEST HIT to saveWebToken', [
            'raw_all' => $request->all(),
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
            'user' => $request->user()?->only(['id', 'name', 'email', 'barber_shop_id']),
        ]);

        $request->validate([
            'fcm_token' => 'required|string',
            'platform' => 'nullable|string',
            'device_name' => 'nullable|string',
            'barber_shop_id' => 'nullable|integer',
        ]);

        $user = $request->user() ?: auth()->user() ?: auth('web')->user();
        $fcmToken = $request->input('fcm_token');
        $platform = $request->input('platform', 'android');
        $deviceName = $request->input('device_name', 'Mobile Device');

        if ($user && $user->name && !str_contains($deviceName, $user->name)) {
            $deviceName .= " - " . $user->name;
        }

        $shopId = $request->input('barber_shop_id')
            ?: $user?->barber_shop_id
            ?: ($user?->activeShop?->id ?? null)
            ?: BarberShop::value('id');

        Log::info('FCM saveWebToken endpoint hit', [
            'fcm_token' => $fcmToken,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'barber_shop_id' => $shopId,
            'platform' => $platform,
            'device_name' => $deviceName,
        ]);

        try {
            // Preserve existing is_sms_gateway status if device was already configured as SMS Gateway
            $existingIsGateway = DeviceToken::where('fcm_token', $fcmToken)->value('is_sms_gateway') ?? false;

            DeviceToken::where('fcm_token', $fcmToken)->delete();

            $deviceToken = DeviceToken::create([
                'fcm_token' => $fcmToken,
                'barber_shop_id' => $shopId,
                'user_id' => $user?->id,
                'platform' => in_array($platform, ['android', 'ios', 'web']) ? $platform : 'android',
                'is_sms_gateway' => (bool) $existingIsGateway,
                'device_name' => $deviceName,
                'last_used_at' => now(),
            ]);

            Log::info('FCM Token successfully saved to DB', ['device_token_id' => $deviceToken->id]);

            return response()->json([
                'success' => true,
                'message' => 'Token-i i pajisjes u regjistrua me sukses te baza e të dhënave!',
                'data' => $deviceToken,
            ]);
        } catch (\Throwable $e) {
            Log::error('FCM saveWebToken DB Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Dështoi ruajtja e token-it te baza e të dhënave: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function setPrimaryGateway(Request $request): JsonResponse
    {
        Log::info('🔥 RAW REQUEST HIT to setPrimaryGateway', [
            'raw_all' => $request->all(),
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
            'user' => $request->user()?->only(['id', 'name', 'email', 'barber_shop_id']),
        ]);

        $request->validate([
            'fcm_token' => 'required|string',
            'is_sms_gateway' => 'required|boolean',
            'device_name' => 'nullable|string',
            'barber_shop_id' => 'nullable|integer',
        ]);

        $user = $request->user() ?: auth()->user() ?: auth('web')->user();
        $fcmToken = $request->input('fcm_token');
        $isSmsGateway = $request->boolean('is_sms_gateway');

        $shopId = $request->input('barber_shop_id')
            ?: $user?->barber_shop_id
            ?: ($user?->activeShop?->id ?? null)
            ?: BarberShop::value('id');

        Log::info('FCM setPrimaryGateway endpoint hit', [
            'fcm_token' => $fcmToken,
            'is_sms_gateway' => $isSmsGateway,
            'user_id' => $user?->id,
            'barber_shop_id' => $shopId,
        ]);

        try {
            if ($isSmsGateway) {
                if ($shopId) {
                    DeviceToken::where('barber_shop_id', $shopId)->update(['is_sms_gateway' => false]);
                }

                // Remove any old rows for this FCM token
                DeviceToken::where('fcm_token', $fcmToken)->delete();

                $deviceToken = DeviceToken::create([
                    'fcm_token' => $fcmToken,
                    'barber_shop_id' => $shopId,
                    'user_id' => $user?->id,
                    'platform' => $request->input('platform', 'android'),
                    'is_sms_gateway' => true,
                    'device_name' => $request->input('device_name', 'Android Device'),
                    'last_used_at' => now(),
                ]);

                // Auto-enable SMS on the salon record if activating gateway
                if ($shopId) {
                    BarberShop::where('id', $shopId)->update(['sms_enabled' => true]);
                }

                Log::info('FCM setPrimaryGateway successfully activated for shop ' . $shopId);

                return response()->json([
                    'success' => true,
                    'is_sms_gateway' => true,
                    'message' => 'Kjo pajisje u caktua si SMS Gateway kryesor i sallonit.',
                    'data' => $deviceToken,
                ]);
            } else {
                DeviceToken::where('fcm_token', $fcmToken)->delete();

                Log::info('FCM setPrimaryGateway deactivated for token');

                return response()->json([
                    'success' => true,
                    'is_sms_gateway' => false,
                    'message' => 'Pajisja u çaktivizua dhe u hoq nga lista.',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('FCM setPrimaryGateway DB Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Dështoi ruajtja e SMS Gateway: ' . $e->getMessage(),
            ], 500);
        }
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
            Log::error("Delete Error: " . $e->getMessage());
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
