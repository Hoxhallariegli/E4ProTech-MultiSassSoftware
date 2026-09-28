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
            'app_name' => 'nullable|string|max:50',
            'primary_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'logo_url' => 'nullable|string',
        ]);

        $shop = $action->execute($validated);

        return response()->json([
            'success' => true,
            'business' => [
                'name' => $shop->name,
                'app_name' => $shop->app_name,
                'logo' => $shop->logo_url,
                'color' => $shop->primary_color,
                'sms_active' => $shop->sms_enabled,
            ]
        ]);
    }
}
