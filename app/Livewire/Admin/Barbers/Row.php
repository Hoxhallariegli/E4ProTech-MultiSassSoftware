<?php

namespace App\Livewire\Admin\Barbers;

use App\Models\Barber;
use Livewire\Component;

class Row extends Component { public Barber $item; public function render() { return view('livewire.admin.barbers.row'); } }