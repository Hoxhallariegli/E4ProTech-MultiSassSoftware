<?php

namespace App\Livewire\Admin\Barbers;

use App\Models\Barber;
use Livewire\Component;

class Row extends Component
{
    public Barber $item;
    public bool $wa_connected = false;

    public function mount()
    {
        try {
            $barberId = $this->item->id;
            $res = \Illuminate\Support\Facades\Http::withHeaders([
                'X-API-KEY' => 'E4ProTech_Secret_Key_2026_Secure'
            ])->timeout(1.5)->get("http://127.0.0.1:3000/api/whatsapp/status?session_key=barber_{$barberId}");

            if ($res->successful()) {
                $this->wa_connected = (bool) $res->json('connected');
            }
        } catch (\Throwable $e) {
            $this->wa_connected = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.barbers.row');
    }
}
