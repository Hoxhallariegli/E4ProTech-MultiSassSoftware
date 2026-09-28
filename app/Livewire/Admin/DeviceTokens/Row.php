<?php

namespace App\Livewire\Admin\DeviceTokens;

use App\Models\DeviceToken;
use Livewire\Component;

class Row extends Component { public DeviceToken $item; public function render() { return view('livewire.admin.device-tokens.row'); } }