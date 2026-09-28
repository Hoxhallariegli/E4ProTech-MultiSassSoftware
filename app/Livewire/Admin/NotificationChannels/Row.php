<?php

namespace App\Livewire\Admin\NotificationChannels;

use App\Models\NotificationChannel;
use Livewire\Component;

class Row extends Component { public NotificationChannel $item; public function render() { return view('livewire.admin.notification-channels.row'); } }