<?php

namespace App\Actions\Branding;

use App\Models\BarberShop;
use Illuminate\Support\Facades\Auth;

class UpdateBrandingAction
{
    public function execute(array $data): BarberShop
    {
        $shopId = Auth::user()->barber_shop_id;
        $shop = BarberShop::findOrFail($shopId);

        $shop->update([
            'app_name' => $data['app_name'] ?? $shop->app_name,
            'primary_color' => $data['primary_color'] ?? $shop->primary_color,
            'logo_url' => $data['logo_url'] ?? $shop->logo_url,
        ]);

        return $shop;
    }
}
