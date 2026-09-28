<?php

namespace App\Livewire\Admin\MessageQueues;

use App\Models\MessageQueue;
use App\Domain\MessageQueue\Queries\MessageQueueListQuery;
use App\Domain\MessageQueue\Actions\DeleteMessageQueueAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('MessageQueues')]
class MessageQueues extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $barber_shop_id = '';
    #[Url(history: true)] public $booking_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'barber_shop_id', 'booking_id', ]); $this->resetPage(); }

    #[On('echo-private:mobile.message-queues,.message-queues.changed')]
    public function onBroadcastCloudChange($event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_message_queues');
        $query = (new MessageQueueListQuery())->handle(['search' => $this->search,             'barber_shop_id' => $this->barber_shop_id,
            'booking_id' => $this->booking_id,
], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.message-queues.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => MessageQueue::sortable(),
            'barberShops' => \App\Models\BarberShop::pluck('name', 'id')->toArray(),
            'bookings' => \App\Models\Booking::with('customer')->get()->pluck('customer.name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, MessageQueue::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteMessageQueue($id, DeleteMessageQueueAction $action) 
    {
        abort_if_cannot('delete_message_queues');
        $item = MessageQueue::find($id);
        if (!$item) { $this->dispatch('toast', message: __('message-queues.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('message-queues.deleted'), type: 'success'); $this->resetPage(); } 
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('message-queues.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('message-queues.delete_error'), type: 'error'); }
    }
}