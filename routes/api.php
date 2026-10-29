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

// Render Cloud Gateway - Session Persistence Public APIs (Protected by X-API-KEY header)
Route::post('whatsapp-cloud-sessions/save', [\App\Http\Controllers\Api\WhatsappSessionCloudController::class, 'saveSession']);
Route::get('whatsapp-cloud-sessions/load', [\App\Http\Controllers\Api\WhatsappSessionCloudController::class, 'loadSessions']);
Route::post('whatsapp-cloud-sessions/delete', [\App\Http\Controllers\Api\WhatsappSessionCloudController::class, 'deleteSession']);

Route::prefix('mobile')->group(function () {
    // Public Mobile Routes
    Route::get('status', fn () => response()->json(['status' => 'online', 'timestamp' => now()->toIso8601String()]));
    Route::get('app-version', [\App\Http\Controllers\Api\Mobile\AppVersionController::class, 'check']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('languages', [\App\Http\Controllers\Api\Mobile\LanguageController::class, 'index']);

    // Device Token Web Registration (Supports Web Session & Sanctum Auth)
    Route::post('device-tokens/save-web-token', [\App\Http\Controllers\Api\Mobile\DeviceTokenController::class, 'saveWebToken'])
        ->middleware(['web']);

    // Protected Mobile & Web API Routes
    Route::middleware(['auth:sanctum'])->group(function () {

        // SMS Gateway Routes
        Route::post('device-tokens/set-primary-gateway', [\App\Http\Controllers\Api\Mobile\DeviceTokenController::class, 'setPrimaryGateway']);
        Route::get('sms-gateway/pending-messages', [\App\Http\Controllers\Api\Mobile\MessageQueueController::class, 'pendingMessages']);
        Route::post('sms-gateway/mark-sent', [\App\Http\Controllers\Api\Mobile\MessageQueueController::class, 'markSent']);

        Route::post('notifications/toggle-module', [\App\Http\Controllers\Api\Mobile\NotificationSettingsController::class, 'toggleModule']);
        Route::post('notifications/toggle-event', [\App\Http\Controllers\Api\Mobile\NotificationSettingsController::class, 'toggleEvent']);

        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('switch-shop', [AuthController::class, 'switchShop']);
        Route::post('ai-assistant', [\App\Http\Controllers\Api\Mobile\AiAssistantController::class, 'process']);

        // Business SaaS Settings
        Route::prefix('business')->group(function () {
            Route::post('toggle-sms', [\App\Http\Controllers\Api\Mobile\BusinessSettingsController::class, 'toggleSms']);
            Route::post('toggle-whatsapp', [\App\Http\Controllers\Api\Mobile\BusinessSettingsController::class, 'toggleWhatsapp']);
            Route::post('update-branding', [\App\Http\Controllers\Api\Mobile\BusinessSettingsController::class, 'updateBranding']);
        });

        // Calendar & Booking Helpers
        Route::get('bookings/calendar', [\App\Http\Controllers\Api\Mobile\BookingController::class, 'calendar']);
        Route::get('bookings/day-schedule', [\App\Http\Controllers\Api\Mobile\BookingController::class, 'daySchedule']);

        // Standard Module Resources
        Route::apiResource('barber-shops', \App\Http\Controllers\Api\Mobile\BarberShopController::class);
        Route::apiResource('barbers', \App\Http\Controllers\Api\Mobile\BarberController::class);
        Route::apiResource('customers', \App\Http\Controllers\Api\Mobile\CustomerController::class);
        Route::apiResource('services', \App\Http\Controllers\Api\Mobile\ServiceController::class);
        Route::apiResource('bookings', \App\Http\Controllers\Api\Mobile\BookingController::class);
        Route::apiResource('reports', \App\Http\Controllers\Api\Mobile\ReportController::class);
        Route::apiResource('reviews', \App\Http\Controllers\Api\Mobile\ReviewController::class);
        Route::apiResource('working-hours', \App\Http\Controllers\Api\Mobile\WorkingHourController::class);
        Route::apiResource('subscriptions', \App\Http\Controllers\Api\Mobile\SubscriptionController::class);
        Route::apiResource('subscription-renewals', \App\Http\Controllers\Api\Mobile\SubscriptionRenewalController::class);
        Route::apiResource('shop-front-page-settings', \App\Http\Controllers\Api\Mobile\ShopFrontPageSettingController::class);
        Route::apiResource('message-templates', \App\Http\Controllers\Api\Mobile\MessageTemplateController::class);
        Route::apiResource('message-queues', \App\Http\Controllers\Api\Mobile\MessageQueueController::class);
        Route::apiResource('message-logs', \App\Http\Controllers\Api\Mobile\MessageLogController::class);
        Route::apiResource('notification-channels', \App\Http\Controllers\Api\Mobile\NotificationChannelController::class);
        Route::apiResource('event-settings', \App\Http\Controllers\Api\Mobile\EventSettingController::class);
        Route::apiResource('bulk-cancellations', \App\Http\Controllers\Api\Mobile\BulkCancellationController::class);
        Route::apiResource('device-tokens', \App\Http\Controllers\Api\Mobile\DeviceTokenController::class);
    });
});
