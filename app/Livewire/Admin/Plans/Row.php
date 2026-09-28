<?php

namespace App\Livewire\Admin\Plans;

use App\Models\Plan;
use Livewire\Component;

class Row extends Component { public Plan $item; public function render() { return view('livewire.admin.plans.row'); } }