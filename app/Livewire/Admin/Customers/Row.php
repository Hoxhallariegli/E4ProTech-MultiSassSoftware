<?php

namespace App\Livewire\Admin\Customers;

use App\Models\Customer;
use Livewire\Component;

class Row extends Component { public Customer $item; public function render() { return view('livewire.admin.customers.row'); } }