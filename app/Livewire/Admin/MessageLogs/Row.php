<?php

namespace App\Livewire\Admin\MessageLogs;

use App\Models\MessageLog;
use Livewire\Component;

class Row extends Component { public MessageLog $item; public function render() { return view('livewire.admin.message-logs.row'); } }