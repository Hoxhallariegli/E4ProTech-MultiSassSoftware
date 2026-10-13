<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Report;
use Livewire\Component;

class Row extends Component { public Report $item; public function render() { return view('livewire.admin.reports.row'); } }