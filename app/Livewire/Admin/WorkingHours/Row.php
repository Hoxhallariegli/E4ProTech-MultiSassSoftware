<?php

namespace App\Livewire\Admin\WorkingHours;

use App\Models\WorkingHour;
use Livewire\Component;

class Row extends Component { public WorkingHour $item; public function render() { return view('livewire.admin.working-hours.row'); } }