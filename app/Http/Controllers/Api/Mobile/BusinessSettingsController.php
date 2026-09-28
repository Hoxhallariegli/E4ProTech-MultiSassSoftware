<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Actions\Sms\ToggleSmsGatewayAction;
use App\Actions\Branding\UpdateBrandingAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessSettingsController extends Controller
{
    public function toggleSms(Request $request, ToggleSmsGatewayAction $action)
    {
        $request->validate(['enabled' => 'required|boolean']);

        $status = $action->execute($request->enabled);

        return response()->json([
            'success' => true,
            'sms_enabled' => $status
        ]);
    }

    public function updateBranding(Request $request, UpdateBrandingAction $action)
    {
        $validated = $request->validate([
            'app_name' => 'nullable|string|max:100',
            'name' => 'nullable|string|max:100',
            'primary_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'logo_url' => 'nullable|string',
            'logo' => 'nullable',
        ]);

        if ($request->hasFile('logo')) {
            $path = app(\App\Services\ImageUploadService::class)->upload($request->file('logo'), 'uploads/barber-shops', 500, 80);
            $validated['logo'] = $path;
        }

        $shop = $action->execute($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profilin dhe logon e ruajtët me sukses!',
            'business' => [
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
            ]
        ]);
    }
}
