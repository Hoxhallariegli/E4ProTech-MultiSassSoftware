<?php

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use Livewire\Component;

class Row extends Component { public Payment $item; public function render() { return view('livewire.admin.payments.row'); } }