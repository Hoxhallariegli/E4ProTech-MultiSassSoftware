<?php

namespace App\Livewire\Admin\BarberShops;

use App\Models\BarberShop;
use App\Domain\BarberShop\Queries\BarberShopListQuery;
use App\Domain\BarberShop\Actions\DeleteBarberShopAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

#[Title('BarberShops')]
class BarberShops extends Component
{
        use WithPagination, WithFileUploads;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $owner_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'owner_id', ]); $this->resetPage(); }

    #[On('echo-private:mobile.barber-shops,.barber-shops.changed')]
    public function onBroadcastCloudChange($event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_barber_shops');
        $query = (new BarberShopListQuery())->handle(['search' => $this->search,             'owner_id' => $this->owner_id,
], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.barber-shops.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => BarberShop::sortable(),
            'owners' => \App\Models\User::pluck('name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, BarberShop::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteBarberShop($id, DeleteBarberShopAction $action) 
    {
        abort_if_cannot('delete_barber_shops');
        $item = BarberShop::find($id);
        if (!$item) { $this->dispatch('toast', message: __('barber-shops.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('barber-shops.deleted'), type: 'success'); $this->resetPage(); } 
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('barber-shops.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('barber-shops.delete_error'), type: 'error'); }
    }
}