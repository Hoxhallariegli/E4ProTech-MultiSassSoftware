<?php

namespace App\Livewire\Admin\NotificationChannels;

use App\Models\NotificationChannel;
use App\Domain\NotificationChannel\Queries\NotificationChannelListQuery;
use App\Domain\NotificationChannel\Actions\DeleteNotificationChannelAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('NotificationChannels')]
class NotificationChannels extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $barber_shop_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'barber_shop_id', ]); $this->resetPage(); }

    #[On('echo-private:mobile.notification-channels,.notification-channels.changed')]
    public function onBroadcastCloudChange($event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_notification_channels');
        $query = (new NotificationChannelListQuery())->handle(['search' => $this->search,             'barber_shop_id' => $this->barber_shop_id,
], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.notification-channels.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => NotificationChannel::sortable(),
            'barberShops' => \App\Models\BarberShop::pluck('name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, NotificationChannel::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteNotificationChannel($id, DeleteNotificationChannelAction $action) 
    {
        abort_if_cannot('delete_notification_channels');
        $item = NotificationChannel::find($id);
        if (!$item) { $this->dispatch('toast', message: __('notification-channels.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('notification-channels.deleted'), type: 'success'); $this->resetPage(); } 
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('notification-channels.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('notification-channels.delete_error'), type: 'error'); }
    }
}