<?php

namespace App\Livewire\Admin\MessageQueues;

use App\Models\MessageQueue;
use App\Jobs\SendSmsToGatewayJob;
use Livewire\Component;

class Row extends Component
{
    public MessageQueue $item;

    public function sendNow()
    {
        try {
            $this->item->update([
                'scheduled_at' => now(),
            ]);

            SendSmsToGatewayJob::dispatch($this->item->id);

            $this->dispatch('toast', message: 'Mesazhi u dërgua me sukses!', type: 'success');
            $this->dispatch('refreshComponent');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Gabim gjatë dërgimit: ' . $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.message-queues.row');
    }
}
