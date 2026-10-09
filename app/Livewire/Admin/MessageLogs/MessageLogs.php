<?php

namespace App\Livewire\Admin\MessageLogs;

use App\Models\MessageLog;
use App\Domain\MessageLog\Queries\MessageLogListQuery;
use App\Domain\MessageLog\Actions\DeleteMessageLogAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('MessageLogs')]
class MessageLogs extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $barber_shop_id = '';
    #[Url(history: true)] public $customer_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = false;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'barber_shop_id', 'customer_id', ]); $this->resetPage(); }

    #[On('echo-private:mobile.message-logs,.message-logs.changed')]
    public function onBroadcastCloudChange($event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_message_logs');
        $query = (new MessageLogListQuery())->handle(['search' => $this->search,             'barber_shop_id' => $this->barber_shop_id,
            'customer_id' => $this->customer_id,
], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.message-logs.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => MessageLog::sortable(),
            'barberShops' => \App\Models\BarberShop::pluck('name', 'id')->toArray(),
            'customers' => \App\Models\Customer::pluck('name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, MessageLog::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteMessageLog($id, DeleteMessageLogAction $action)
    {
        abort_if_cannot('delete_message_logs');
        $item = MessageLog::find($id);
        if (!$item) { $this->dispatch('toast', message: __('message-logs.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('message-logs.deleted'), type: 'success'); $this->resetPage(); }
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('message-logs.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('message-logs.delete_error'), type: 'error'); }
    }
}
