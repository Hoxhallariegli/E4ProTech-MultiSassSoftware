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
            $res = \Illuminate\Support\Facades\Http::withHeaders([
                'X-API-KEY' => 'E4ProTech_Secret_Key_2026_Secure'
            ])->timeout(1.5)->get("http://127.0.0.1:3000/api/whatsapp/status?shop_id={$shopId}");

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
