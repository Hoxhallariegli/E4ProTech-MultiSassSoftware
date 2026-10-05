<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


use App\Http\Controllers\Api\Mobile\AuthController;
Route::get('/health', fn () => response()->json(['ok' => true]))->withoutMiddleware(['auth:sanctum']);
Route::get('mobile/status', fn () => response()->json(['status' => 'online', 'timestamp' => now()->toIso8601String()]));
Route::get('app-version', [\App\Http\Controllers\Api\Mobile\AppVersionController::class, 'check']);
Route::get('mobile/app-version', [\App\Http\Controllers\Api\Mobile\AppVersionController::class, 'check']);
Route::post('mobile/login', [AuthController::class, 'login']);
Route::get('mobile/languages', [\App\Http\Controllers\Api\Mobile\LanguageController::class, 'index']);

// Public Device Token & SMS Gateway status routes
Route::post('mobile/device-tokens/save-web-token', [\App\Http\Controllers\Api\Mobile\DeviceTokenController::class, 'saveWebToken']);
Route::post('mobile/device-tokens/set-primary-gateway', [\App\Http\Controllers\Api\Mobile\DeviceTokenController::class, 'setPrimaryGateway']);
Route::post('mobile/sms-gateway/mark-sent', [\App\Http\Controllers\Api\Mobile\MessageQueueController::class, 'markSent']);

Route::middleware(['auth:sanctum'])->prefix('mobile')->group(function () {

    Route::post('device-tokens/save-web-token', [\App\Http\Controllers\Api\Mobile\DeviceTokenController::class, 'saveWebToken']);
    Route::get('sms-gateway/pending-messages', [\App\Http\Controllers\Api\Mobile\MessageQueueController::class, 'pendingMessages']);
    Route::post('sms-gateway/mark-sent', [\App\Http\Controllers\Api\Mobile\MessageQueueController::class, 'markSent']);

    Route::get('barber-shops', function (\Illuminate\Http\Request $request) {
        $search = trim((string) $request->input('search', ''));
        // Spatie Global scope handled in model
        $query = \App\Models\BarberShop::query();
        if ($search !== '') { $query->where('name', 'like', '%' . $search . '%'); }
        $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name])->values();
        return response()->json(['data' => $items]);
    });

    Route::get('services', function (\Illuminate\Http\Request $request) {
        $search = trim((string) $request->input('search', ''));
        $query = \App\Models\Service::query();
        if ($search !== '') { $query->where('name', 'like', '%' . $search . '%'); }
        $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name])->values();
        return response()->json(['data' => $items]);
    });
    Route::get('barbers', function (\Illuminate\Http\Request $request) {
        $search = trim((string) $request->input('search', ''));
        $query = \App\Models\Barber::query();
        if ($search !== '') { $query->where('name', 'like', '%' . $search . '%'); }
        $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name])->values();
        return response()->json(['data' => $items]);
    });

    Route::get('ba-services', function (\Illuminate\Http\Request $request) {
        $search = trim((string) $request->input('search', ''));
        $query = \App\Models\BaService::query();
        if ($search !== '') { $query->where('name', 'like', '%' . $search . '%'); }
        $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name])->values();
        return response()->json(['data' => $items]);
    });
    Route::get('ba-barbers', function (\Illuminate\Http\Request $request) {
        $search = trim((string) $request->input('search', ''));
        $query = \App\Models\BaBarber::query();
        if ($search !== '') { $query->where('name', 'like', '%' . $search . '%'); }
        $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name])->values();
        return response()->json(['data' => $items]);
    });
    Route::get('customers', function (\Illuminate\Http\Request $request) {
        $search = trim((string) $request->input('search', ''));
        $query = \App\Models\Customer::query();
        if ($search !== '') { $query->where('name', 'like', '%' . $search . '%')->orWhere('phone', 'like', '%' . $search . '%'); }
        $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name . ($item->phone ? ' (' . $item->phone . ')' : '')])->values();
        return response()->json(['data' => $items]);
    });

    Route::get('bookings', function (\Illuminate\Http\Request $request) {
        $search = trim((string) $request->input('search', ''));
        $query = \App\Models\Booking::query()->with('customer');
        if ($search !== '') { $query->whereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%")); }
        $items = $query->latest('id')->limit(50)->get()->map(fn ($item) => [
            'id' => $item->getKey(),
            'name' => 'Takimi #' . $item->id . ' - ' . ($item->customer?->name ?? 'Klient') . ' (' . ($item->appointment_at?->format('d/m H:i') ?? '') . ')'
        ])->values();
        return response()->json(['data' => $items]);
    });

    Route::get('users', function (\Illuminate\Http\Request $request) {
        $search = trim((string) $request->input('search', ''));
        // Local scope for active shop
        $query = \App\Models\User::forActiveShop();
        if ($search !== '') { $query->where('name', 'like', '%' . $search . '%'); }
        $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name])->values();
        return response()->json(['data' => $items]);
    });

    // Notification Settings
    Route::get('notifications/settings', [\App\Http\Controllers\Api\Mobile\NotificationSettingsController::class, 'index']);
    Route::post('notifications/toggle-module', [\App\Http\Controllers\Api\Mobile\NotificationSettingsController::class, 'toggleModule']);
    Route::post('notifications/toggle-event', [\App\Http\Controllers\Api\Mobile\NotificationSettingsController::class, 'toggleEvent']);

    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::post('switch-shop', [AuthController::class, 'switchShop']);

    // Business SaaS Settings
    Route::prefix('business')->group(function () {
        Route::post('toggle-sms', [\App\Http\Controllers\Api\Mobile\BusinessSettingsController::class, 'toggleSms']);
        Route::post('update-branding', [\App\Http\Controllers\Api\Mobile\BusinessSettingsController::class, 'updateBranding']);
    });
});

Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {
    Route::post('device-tokens/set-primary-gateway', [\App\Http\Controllers\Api\Mobile\DeviceTokenController::class, 'setPrimaryGateway']);
    Route::apiResource('working-hours', \App\Http\Controllers\Api\Mobile\WorkingHourController::class);
    Route::apiResource('reviews', \App\Http\Controllers\Api\Mobile\ReviewController::class);
    Route::apiResource('device-tokens', \App\Http\Controllers\Api\Mobile\DeviceTokenController::class);
    Route::apiResource('message-logs', \App\Http\Controllers\Api\Mobile\MessageLogController::class);
    Route::apiResource('message-queues', \App\Http\Controllers\Api\Mobile\MessageQueueController::class);
    Route::apiResource('payments', \App\Http\Controllers\Api\Mobile\PaymentController::class);
    Route::get('bookings/calendar', [\App\Http\Controllers\Api\Mobile\BookingController::class, 'calendar']);
    Route::get('bookings/day-schedule', [\App\Http\Controllers\Api\Mobile\BookingController::class, 'daySchedule']);
    Route::apiResource('bookings', \App\Http\Controllers\Api\Mobile\BookingController::class);
    Route::apiResource('message-templates', \App\Http\Controllers\Api\Mobile\MessageTemplateController::class);
    Route::apiResource('event-settings', \App\Http\Controllers\Api\Mobile\EventSettingController::class);
    Route::apiResource('notification-channels', \App\Http\Controllers\Api\Mobile\NotificationChannelController::class);
    Route::apiResource('customers', \App\Http\Controllers\Api\Mobile\CustomerController::class);

    Route::apiResource('services', \App\Http\Controllers\Api\Mobile\ServiceController::class);
    Route::apiResource('barbers', \App\Http\Controllers\Api\Mobile\BarberController::class);
    Route::apiResource('subscriptions', \App\Http\Controllers\Api\Mobile\SubscriptionController::class);
    Route::apiResource('plans', \App\Http\Controllers\Api\Mobile\PlanController::class);









































































































    Route::apiResource('barber-shops', \App\Http\Controllers\Api\Mobile\BarberShopController::class);
































































































































































































































































    Route::get('realtime-events', function (\Illuminate\Http\Request $request) {
        $search = trim((string) $request->input('search', ''));
        $query = \App\Models\RealtimeEvent::query();
        if ($search !== '') { $query->where('event', 'like', '%' . $search . '%'); }
        $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->event])->values();
        return response()->json(['data' => $items]);
    });


});
