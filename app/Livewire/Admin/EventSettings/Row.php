<?php

namespace App\Livewire\Admin\EventSettings;

use App\Models\EventSetting;
use Livewire\Component;

class Row extends Component { public EventSetting $item; public function render() { return view('livewire.admin.event-settings.row'); } }