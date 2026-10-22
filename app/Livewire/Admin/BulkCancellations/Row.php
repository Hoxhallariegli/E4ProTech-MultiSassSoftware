<?php

namespace App\Livewire\Admin\BulkCancellations;

use App\Models\BulkCancellation;
use Livewire\Component;

class Row extends Component { public BulkCancellation $item; public function render() { return view('livewire.admin.bulk-cancellations.row'); } }