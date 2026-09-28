<?php

namespace App\Http\Controllers;

use App\Models\BarberShop;
use Illuminate\Http\Request;

class ShopLandingController extends Controller
{
    public function show(string $slug)
    {
        $shop = BarberShop::where('slug', $slug)->where('active', true)->firstOrFail();
        $staff = \App\Models\Barber::where('barber_shop_id', $shop->id)->where('active', true)->get();
        $services = \App\Models\Service::where('barber_shop_id', $shop->id)->where('active', true)->get();
        $workingHours = \App\Models\WorkingHour::whereIn('barber_id', $staff->pluck('id'))->get();

        return view('shop-landing', [
            'shop' => $shop,
            'staff' => $staff,
            'services' => $services,
            'workingHours' => $workingHours,
        ]);
    }
}
