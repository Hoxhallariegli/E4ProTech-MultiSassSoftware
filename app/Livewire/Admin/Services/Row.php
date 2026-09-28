<?php

namespace App\Livewire\Admin\Services;

use App\Models\Service;
use Livewire\Component;

class Row extends Component { public Service $item; public function render() { return view('livewire.admin.services.row'); } }