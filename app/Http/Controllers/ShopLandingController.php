<?php

namespace App\Http\Controllers;

use App\Models\BarberShop;
use Illuminate\Http\Request;

class ShopLandingController extends Controller
{
    public function show(string $slug)
    {
        $shop = BarberShop::where('slug', $slug)->first();

        if (!$shop) {
            abort(404, 'Salloni nuk u gjet.');
        }

        $staff = \App\Models\Barber::withoutGlobalScope('barber_shop_access')
            ->where('barber_shop_id', $shop->id)
            ->get();

        $services = \App\Models\Service::withoutGlobalScope('barber_shop_access')
            ->where('barber_shop_id', $shop->id)
            ->get();

        $workingHours = \App\Models\WorkingHour::whereIn('barber_id', $staff->pluck('id'))->get();

        return view('shop-landing', [
            'shop' => $shop,
            'staff' => $staff,
            'services' => $services,
            'workingHours' => $workingHours,
        ]);
    }
}
