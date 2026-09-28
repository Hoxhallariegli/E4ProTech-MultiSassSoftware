<?php

namespace App\Actions\Sms;

use App\Models\BarberShop;
use Illuminate\Support\Facades\Auth;

class ToggleSmsGatewayAction
{
    public function execute(bool $enabled): bool
    {
        $shopId = Auth::user()->barber_shop_id;

        if (!$shopId) {
            return false;
        }

        $shop = BarberShop::findOrFail($shopId);
        $shop->sms_enabled = $enabled;
        $shop->save();

        return $shop->sms_enabled;
    }
}
