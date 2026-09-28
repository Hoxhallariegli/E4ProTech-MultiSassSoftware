<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredencialet janë të gabuara.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Llogaria juaj është jo-aktive.'],
            ]);
        }

        // Set current team ID for permission scoping
        setPermissionsTeamId($user->barber_shop_id ?: 0);

        return response()->json([
            'token' => $user->createToken($request->device_name)->plainTextToken,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'barber_shop_id' => $user->barber_shop_id,
                'image' => $user->image ? asset(ltrim($user->image, '/')) : null,
                'is_admin' => $user->is_global_admin,
                'permissions' => $user->getPermissionsFlattened(),
                'accessible_shops' => (function() use ($user) {
                    if ($user->is_global_admin) {
                        return \App\Models\BarberShop::get(['id', 'name']);
                    }

                    return \App\Models\BarberShop::whereIn('id',
                        $user->barberShops()->pluck('barber_shops.id')->filter()->unique()->values()
                    )->get(['id', 'name']);
                })(),
                'business' => (function() use ($user) {
                    if (!$user->barber_shop_id) return null;

                    // Security check: only return business data if user has access
                    if (!$user->is_global_admin && !$user->barberShops()->where('barber_shops.id', $user->barber_shop_id)->exists()) {
                        return null;
                    }

                    $shop = $user->barberShop;
                    if (!$shop) return null;

                    return [
                        'name' => $shop->name,
                        'app_name' => $shop->app_name ?: $shop->name,
                        'logo' => $shop->logo_url,
                        'color' => $shop->primary_color,
                        'sms_active' => (bool) $shop->sms_enabled,
                        'plan_name' => $shop->active_plan_name,
                        'trial_days_left' => $shop->days_left,
                        'subscription_status' => $shop->days_left > 0 ? 'active' : 'expired',
                        'business_type' => $shop->business_type ?? 'general',
                        'staff_label' => $shop->resolved_staff_label,
                        'staff_label_plural' => $shop->resolved_staff_label_plural,
                        'shop_label' => $shop->resolved_shop_label,
                        'service_label' => $shop->resolved_service_label,
                    ];
                })(),
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function switchShop(Request $request)
    {
        $request->validate([
            'barber_shop_id' => 'required|exists:barber_shops,id',
        ]);

        $user = $request->user();
        $shopId = $request->barber_shop_id;

        // Security check
        $hasAccess = false;
        if (\Illuminate\Support\Facades\DB::table('model_has_roles')
            ->where('model_id', $user->id)
            ->where('barber_shop_id', 0)
            ->exists()) {
            $hasAccess = true;
        } else {
            $hasAccess = \App\Models\BarberShop::where('id', $shopId)
                ->where(function($q) use ($user) {
                    $q->where('owner_id', $user->id)
                      ->orWhereHas('users', function($qu) use ($user) {
                          $qu->where('users.id', $user->id);
                      });
                })->exists();
        }

        if (!$hasAccess) {
            return response()->json(['message' => 'Nuk keni akses në këtë dyqan.'], 403);
        }

        $user->barber_shop_id = $shopId;
        $user->save();

        // Spatie Teams: Flush cache and set new team ID
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        setPermissionsTeamId($shopId);

        return $this->me($request);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        // Set current team ID for permission scoping
        setPermissionsTeamId($user->barber_shop_id ?: 0);

        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'barber_shop_id' => $user->barber_shop_id,
                'image' => $user->image ? asset(ltrim($user->image, '/')) : null,
                'is_admin' => $user->is_global_admin,
                'permissions' => $user->getPermissionsFlattened(),
                'accessible_shops' => (function() use ($user) {
                    if ($user->is_global_admin) {
                        return \App\Models\BarberShop::get(['id', 'name']);
                    }

                    return \App\Models\BarberShop::whereIn('id',
                        $user->barberShops()->pluck('barber_shops.id')->filter()->unique()->values()
                    )->get(['id', 'name']);
                })(),
                'business' => (function() use ($user) {
                    if (!$user->barber_shop_id) return null;

                    // Security check: only return business data if user has access
                    if (!$user->is_global_admin && !$user->barberShops()->where('barber_shops.id', $user->barber_shop_id)->exists()) {
                        return null;
                    }

                    $shop = $user->barberShop;
                    if (!$shop) return null;

                    return [
                        'name' => $shop->name,
                        'app_name' => $shop->app_name ?: $shop->name,
                        'logo' => $shop->logo_url,
                        'color' => $shop->primary_color,
                        'sms_active' => (bool) $shop->sms_enabled,
                        'trial_days_left' => $shop->expires_at ? (int) now()->diffInDays($shop->expires_at, false) : 0,
                    ];
                })(),
            ]
        ]);
    }
}
