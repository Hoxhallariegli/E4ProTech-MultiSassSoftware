<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Barber;
use App\Models\DeviceToken;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render(): View
    {
        $user = auth()->user();
        $shop = $user?->barberShop;

        $staffCount = $shop ? Barber::where('barber_shop_id', $shop->id)->count() : 0;
        $servicesCount = $shop ? Service::where('barber_shop_id', $shop->id)->count() : 0;

        // Check if SMS Gateway is enabled for shop AND has an active primary SMS gateway device token
        $isSmsEnabled = $shop ? (bool) $shop->sms_enabled : false;
        $activeSmsTokens = $shop
            ? DeviceToken::where('barber_shop_id', $shop->id)->where('is_sms_gateway', true)->count()
            : 0;
        $smsGatewayOk = $isSmsEnabled && ($activeSmsTokens > 0);

        // Check if shop has a registered Firebase FCM device token (APK App connected)
        $hasFcmDevice = $shop
            ? DeviceToken::where('barber_shop_id', $shop->id)->exists()
            : false;

        // Logical rule: If SMS Gateway is active, the APK App is definitively connected!
        if ($smsGatewayOk) {
            $hasFcmDevice = true;
        }

        // Calculate Onboarding Progress
        $completedSteps = 0;
        if ($staffCount > 0) $completedSteps++;
        if ($servicesCount > 0) $completedSteps++;
        if ($hasFcmDevice) $completedSteps++;
        if ($smsGatewayOk) $completedSteps++;

        $progressPercentage = (int) round(($completedSteps / 4) * 100);

        // Read dynamic APK version from version.json
        $apkVersion = '1.0.111';
        try {
            $versionFilePath = base_path('version.json');
            if (file_exists($versionFilePath)) {
                $versionData = json_decode(file_get_contents($versionFilePath), true);
                if (!empty($versionData['latest_version'])) {
                    $apkVersion = (string) $versionData['latest_version'];
                }
            }
        } catch (\Throwable $e) {}

        return view('livewire.admin.dashboard', [
            'user' => $user,
            'shop' => $shop,
            'staffCount' => $staffCount,
            'servicesCount' => $servicesCount,
            'hasFcmDevice' => $hasFcmDevice,
            'isSmsEnabled' => $isSmsEnabled,
            'activeSmsTokens' => $activeSmsTokens,
            'smsGatewayOk' => $smsGatewayOk,
            'completedSteps' => $completedSteps,
            'progressPercentage' => $progressPercentage,
            'apkVersion' => $apkVersion,
        ]);
    }
}
