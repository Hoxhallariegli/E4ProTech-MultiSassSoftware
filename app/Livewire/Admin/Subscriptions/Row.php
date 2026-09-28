<?php

namespace App\Livewire\Admin\Subscriptions;

use App\Models\Subscription;
use Livewire\Component;

class Row extends Component { public Subscription $item; public function render() { return view('livewire.admin.subscriptions.row'); } }