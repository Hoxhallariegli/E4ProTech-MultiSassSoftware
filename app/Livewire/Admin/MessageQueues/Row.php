<?php

namespace App\Livewire\Admin\MessageQueues;

use App\Models\MessageQueue;
use Livewire\Component;

class Row extends Component { public MessageQueue $item; public function render() { return view('livewire.admin.message-queues.row'); } }