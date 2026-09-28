<?php

namespace App\Livewire\Admin\Services;

use App\Models\Service;
use App\Domain\Service\Queries\ServiceListQuery;
use App\Domain\Service\Actions\DeleteServiceAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Services')]
class Services extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $barber_shop_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'barber_shop_id', ]); $this->resetPage(); }

    #[On('echo-private:mobile.services,.services.changed')]
    public function onBroadcastCloudChange($event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_services');
        $query = (new ServiceListQuery())->handle(['search' => $this->search,             'barber_shop_id' => $this->barber_shop_id,
], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.services.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => Service::sortable(),
            'barberShops' => \App\Models\BarberShop::pluck('name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, Service::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteService($id, DeleteServiceAction $action) 
    {
        abort_if_cannot('delete_services');
        $item = Service::find($id);
        if (!$item) { $this->dispatch('toast', message: __('services.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('services.deleted'), type: 'success'); $this->resetPage(); } 
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('services.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('services.delete_error'), type: 'error'); }
    }
}