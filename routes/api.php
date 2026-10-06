<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Mobile\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Health Check & Version Routes
Route::get('/health', fn () => response()->json(['ok' => true]))->withoutMiddleware(['auth:sanctum']);
Route::get('app-version', [\App\Http\Controllers\Api\Mobile\AppVersionController::class, 'check']);

Route::prefix('mobile')->group(function () {
    // Public Mobile Routes
    Route::get('status', fn () => response()->json(['status' => 'online', 'timestamp' => now()->toIso8601String()]));
    Route::get('app-version', [\App\Http\Controllers\Api\Mobile\AppVersionController::class, 'check']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('languages', [\App\Http\Controllers\Api\Mobile\LanguageController::class, 'index']);

    // Device Token Web Registration (Supports Web Session & Sanctum Auth)
    Route::post('device-tokens/save-web-token', [\App\Http\Controllers\Api\Mobile\DeviceTokenController::class, 'saveWebToken']);

    // Protected Mobile & Web API Routes
    Route::middleware(['auth:sanctum'])->group(function () {

        // SMS Gateway Routes
        Route::post('device-tokens/set-primary-gateway', [\App\Http\Controllers\Api\Mobile\DeviceTokenController::class, 'setPrimaryGateway']);
        Route::get('sms-gateway/pending-messages', [\App\Http\Controllers\Api\Mobile\MessageQueueController::class, 'pendingMessages']);
        Route::post('sms-gateway/mark-sent', [\App\Http\Controllers\Api\Mobile\MessageQueueController::class, 'markSent']);

        // Search Dropdown Routes for Livewire Admin & Mobile
        Route::get('barber-shops', function (\Illuminate\Http\Request $request) {
            $search = trim((string) $request->input('search', ''));
            $query = \App\Models\BarberShop::query();
            if ($search !== '') { $query->where('name', 'like', '%' . $search . '%'); }
            $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name])->values();
            return response()->json(['data' => $items]);
        });

        Route::get('barbers', function (\Illuminate\Http\Request $request) {
            $search = trim((string) $request->input('search', ''));
            $query = \App\Models\Barber::forActiveShop();
            if ($search !== '') { $query->where('name', 'like', '%' . $search . '%'); }
            $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name])->values();
            return response()->json(['data' => $items]);
        });

        Route::get('ba-barbers', function (\Illuminate\Http\Request $request) {
            $search = trim((string) $request->input('search', ''));
            $query = \App\Models\Barber::forActiveShop();
            if ($search !== '') { $query->where('name', 'like', '%' . $search . '%'); }
            $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name])->values();
            return response()->json(['data' => $items]);
        });

        Route::get('services', function (\Illuminate\Http\Request $request) {
            $search = trim((string) $request->input('search', ''));
            $query = \App\Models\Service::forActiveShop();
            if ($search !== '') { $query->where('name', 'like', '%' . $search . '%'); }
            $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name, 'price' => $item->price, 'duration_minutes' => $item->duration_minutes])->values();
            return response()->json(['data' => $items]);
        });

        Route::get('ba-services', function (\Illuminate\Http\Request $request) {
            $search = trim((string) $request->input('search', ''));
            $query = \App\Models\Service::forActiveShop();
            if ($search !== '') { $query->where('name', 'like', '%' . $search . '%'); }
            $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name, 'price' => $item->price, 'duration_minutes' => $item->duration_minutes])->values();
            return response()->json(['data' => $items]);
        });

        Route::get('customers', function (\Illuminate\Http\Request $request) {
            $search = trim((string) $request->input('search', ''));
            $query = \App\Models\Customer::forActiveShop();
            if ($search !== '') { $query->where('name', 'like', '%' . $search . '%'); }
            $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name])->values();
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
            $query = \App\Models\User::forActiveShop();
            if ($search !== '') { $query->where('name', 'like', '%' . $search . '%'); }
            $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name])->values();
            return response()->json(['data' => $items]);
        });

        Route::get('realtime-events', function (\Illuminate\Http\Request $request) {
            $search = trim((string) $request->input('search', ''));
            $query = \App\Models\RealtimeEvent::query();
            if ($search !== '') { $query->where('event', 'like', '%' . $search . '%'); }
            $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->event])->values();
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

        // Calendar & Booking Helpers
        Route::get('bookings/calendar', [\App\Http\Controllers\Api\Mobile\BookingController::class, 'calendar']);
        Route::get('bookings/day-schedule', [\App\Http\Controllers\Api\Mobile\BookingController::class, 'daySchedule']);

        // Standard Module Resources
        Route::apiResource('barber-shops', \App\Http\Controllers\Api\Mobile\BarberShopController::class);
        Route::apiResource('barbers', \App\Http\Controllers\Api\Mobile\BarberController::class);
        Route::apiResource('services', \App\Http\Controllers\Api\Mobile\ServiceController::class);
        Route::apiResource('customers', \App\Http\Controllers\Api\Mobile\CustomerController::class);
        Route::apiResource('bookings', \App\Http\Controllers\Api\Mobile\BookingController::class);
        Route::apiResource('payments', \App\Http\Controllers\Api\Mobile\PaymentController::class);
        Route::apiResource('message-templates', \App\Http\Controllers\Api\Mobile\MessageTemplateController::class);
        Route::apiResource('message-queues', \App\Http\Controllers\Api\Mobile\MessageQueueController::class);
        Route::apiResource('message-logs', \App\Http\Controllers\Api\Mobile\MessageLogController::class);
        Route::apiResource('device-tokens', \App\Http\Controllers\Api\Mobile\DeviceTokenController::class);
        Route::apiResource('event-settings', \App\Http\Controllers\Api\Mobile\EventSettingController::class);
        Route::apiResource('notification-channels', \App\Http\Controllers\Api\Mobile\NotificationChannelController::class);
        Route::apiResource('plans', \App\Http\Controllers\Api\Mobile\PlanController::class);
        Route::apiResource('subscriptions', \App\Http\Controllers\Api\Mobile\SubscriptionController::class);
        Route::apiResource('reviews', \App\Http\Controllers\Api\Mobile\ReviewController::class);
        Route::apiResource('working-hours', \App\Http\Controllers\Api\Mobile\WorkingHourController::class);
    });
});
