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

        $updateData = [];
        if (isset($data['app_name']) && !empty($data['app_name'])) {
            $updateData['app_name'] = $data['app_name'];
            $updateData['name'] = $data['app_name'];
        }
        if (isset($data['name']) && !empty($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['primary_color']) && !empty($data['primary_color'])) {
            $updateData['primary_color'] = $data['primary_color'];
        }
        if (isset($data['logo'])) {
            $updateData['logo'] = $data['logo'];
        } elseif (isset($data['logo_url'])) {
            $updateData['logo'] = $data['logo_url'];
        }

        if (!empty($updateData)) {
            $shop->update($updateData);
        }

        return $shop;
    }
}
