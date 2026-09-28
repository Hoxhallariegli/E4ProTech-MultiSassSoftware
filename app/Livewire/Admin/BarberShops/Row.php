<?php

namespace App\Livewire\Admin\BarberShops;

use App\Models\BarberShop;
use Livewire\Component;

class Row extends Component { public BarberShop $item; public function render() { return view('livewire.admin.barber-shops.row'); } }