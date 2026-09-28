<?php

namespace App\Livewire\Admin\Bookings;

use App\Models\Booking;
use Livewire\Component;

class Row extends Component { public Booking $item; public function render() { return view('livewire.admin.bookings.row'); } }