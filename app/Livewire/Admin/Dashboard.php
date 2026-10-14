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
        $activeSmsTokens = $shop ? DeviceToken::where('barber_shop_id', $shop->id)->where('is_sms_gateway', true)->count() : 0;

        // Calculate Onboarding Progress
        $completedSteps = 0;
        if ($staffCount > 0) $completedSteps++;
        if ($servicesCount > 0) $completedSteps++;
        $completedSteps++; // APK link is available
        if ($activeSmsTokens > 0 || ($shop && $shop->sms_enabled)) $completedSteps++;

        $progressPercentage = (int) round(($completedSteps / 4) * 100);

        return view('livewire.admin.dashboard', [
            'user' => $user,
            'shop' => $shop,
            'staffCount' => $staffCount,
            'servicesCount' => $servicesCount,
            'activeSmsTokens' => $activeSmsTokens,
            'completedSteps' => $completedSteps,
            'progressPercentage' => $progressPercentage,
        ]);
    }
}
