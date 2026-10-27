<?php

namespace App\Livewire\Admin\BarberShops;

use App\Models\BarberShop;
use Livewire\Component;

class Row extends Component
{
    public BarberShop $item;
    public bool $wa_connected = false;

    public function mount()
    {
        try {
            $shopId = $this->item->id;
            $gatewayUrl = config('services.whatsapp.gateway_url', 'https://whatsapp-gateway-k6vv.onrender.com/api/whatsapp/send');
            $baseUrl = str_replace('/send', '', $gatewayUrl);

            $res = \Illuminate\Support\Facades\Http::withHeaders([
                'X-API-KEY' => config('services.whatsapp.secret', 'E4ProTech_Secret_Key_2026_Secure')
            ])->timeout(2.5)->get("{$baseUrl}/status?session_key=shop_{$shopId}");

            if ($res->successful()) {
                $this->wa_connected = (bool) $res->json('connected');
            }
        } catch (\Throwable $e) {
            $this->wa_connected = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.barber-shops.row');
    }
}
