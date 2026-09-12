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

Route::post('mobile/login', [AuthController::class, 'login']);
Route::get('mobile/languages', [\App\Http\Controllers\Api\Mobile\LanguageController::class, 'index']);

Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {
    Route::apiResource('test-modules', \App\Http\Controllers\Api\Mobile\TestModuleController::class);
    


























    Route::get('users', function (\Illuminate\Http\Request $request) {
        $search = trim((string) $request->input('search', ''));
        $query = \App\Models\User::query();
        if ($search !== '') { $query->where('name', 'like', '%' . $search . '%'); }
        $items = $query->limit(50)->get()->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name])->values();
        return response()->json(['data' => $items]);
    });















    Route::get('status', function () {
        return response()->json([
            'api' => 'online',
            'database' => \Illuminate\Support\Facades\DB::connection()->getPdo() ? 'online' : 'offline',
            'cache' => \Illuminate\Support\Facades\Cache::driver()->getStore() instanceof \Illuminate\Cache\FileStore ? 'file-active' : 'redis-live',
        ]);
    });

    // Notification Settings
    Route::get('notifications/settings', [\App\Http\Controllers\Api\Mobile\NotificationSettingsController::class, 'index']);
    Route::post('notifications/toggle-module', [\App\Http\Controllers\Api\Mobile\NotificationSettingsController::class, 'toggleModule']);
    Route::post('notifications/toggle-event', [\App\Http\Controllers\Api\Mobile\NotificationSettingsController::class, 'toggleEvent']);

    Route::post('logout', [AuthController::class, 'logout']);





















});
