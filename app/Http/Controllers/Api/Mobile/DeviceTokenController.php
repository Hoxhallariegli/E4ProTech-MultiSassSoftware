<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\DeviceTokenResource;
use App\Models\DeviceToken;
use App\Models\BarberShop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DeviceTokenController extends Controller
{
    public function saveWebToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'platform' => 'nullable|string',
            'device_name' => 'nullable|string',
            'barber_shop_id' => 'nullable|integer',
        ]);

        $user = $request->user() ?: auth()->user() ?: auth('web')->user();
        if (!$user && $request->filled('user_id')) {
            $user = \App\Models\User::find($request->input('user_id'));
        }

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

        try {
            // Check if explicitly passed or fallback to false when saving web/app tokens
            $isGateway = $request->has('is_sms_gateway')
                ? $request->boolean('is_sms_gateway')
                : (DeviceToken::where('fcm_token', $fcmToken)->value('is_sms_gateway') ?? false);

            DeviceToken::where('fcm_token', $fcmToken)->delete();

            $deviceToken = DeviceToken::create([
                'fcm_token' => $fcmToken,
                'barber_shop_id' => $shopId,
                'user_id' => $user?->id,
                'platform' => in_array($platform, ['android', 'ios', 'web']) ? $platform : 'android',
                'is_sms_gateway' => (bool) $isGateway,
                'device_name' => $deviceName,
                'last_used_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token-i i pajisjes u regjistrua me sukses te baza e të dhënave!',
                'data' => $deviceToken,
            ]);
        } catch (\Throwable $e) {
            Log::error('FCM saveWebToken DB Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Dështoi ruajtja e token-it te baza e të dhënave: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function setPrimaryGateway(Request $request): JsonResponse
    {
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

                // Auto-enable SMS on the salon record
                if ($shopId) {
                    BarberShop::where('id', $shopId)->update(['sms_enabled' => true]);
                    \App\Models\MessageQueue::where('barber_shop_id', $shopId)
                        ->where('status', 'pending')
                        ->where('scheduled_at', '<', now()->subMinutes(15))
                        ->update(['status' => 'failed']);
                }

                Log::info('FCM setPrimaryGateway successfully activated for shop ' . $shopId);

                return response()->json([
                    'success' => true,
                    'is_sms_gateway' => true,
                    'message' => 'Kjo pajisje u caktua si SMS Gateway kryesor i sallonit.',
                    'data' => $deviceToken,
                ]);
            } else {
                DeviceToken::where('fcm_token', $fcmToken)->update(['is_sms_gateway' => false]);
                if ($user?->id) {
                    DeviceToken::where('user_id', $user->id)->update(['is_sms_gateway' => false]);
                }
                if ($shopId) {
                    DeviceToken::where('barber_shop_id', $shopId)->update(['is_sms_gateway' => false]);
                }

                Log::info('FCM setPrimaryGateway deactivated for token and shop ' . $shopId);

                return response()->json([
                    'success' => true,
                    'is_sms_gateway' => false,
                    'message' => 'Pajisja u çaktivizua nga roli SMS Gateway.',
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
        $user = $request->user();

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

        if ($user && !$user->is_global_admin && $user->barber_shop_id) {
            $query->where('barber_shop_id', $user->barber_shop_id);
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
                foreach (['fcm_token', 'device_name'] as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return DeviceTokenResource::collection($items);
    }

    public function show($id)
    {
        $item = DeviceToken::with([
            'barberShop',
            'user',
        ])->findOrFail($id);
        return new DeviceTokenResource($item);
    }

    public function destroy($id): JsonResponse
    {
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
}
