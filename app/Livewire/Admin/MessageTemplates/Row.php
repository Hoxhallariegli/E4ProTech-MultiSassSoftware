<?php

namespace App\Livewire\Admin\MessageTemplates;

use App\Models\MessageTemplate;
use Livewire\Component;

class Row extends Component { public MessageTemplate $item; public function render() { return view('livewire.admin.message-templates.row'); } }