<?php

namespace App\Livewire\Admin\Reviews;

use App\Models\Review;
use Livewire\Component;

class Row extends Component { public Review $item; public function render() { return view('livewire.admin.reviews.row'); } }