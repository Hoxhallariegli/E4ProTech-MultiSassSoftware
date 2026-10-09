<?php

namespace App\Livewire\Admin\TestModules;

use App\Models\TestModule;
use Livewire\Component;

class Row extends Component { public TestModule $item; public function render() { return view('livewire.admin.test-modules.row'); } }