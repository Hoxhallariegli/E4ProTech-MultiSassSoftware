<?php

namespace App\Livewire\Admin\BulkCancellations;

use App\Models\BulkCancellation;
use App\Domain\BulkCancellation\Queries\BulkCancellationListQuery;
use App\Domain\BulkCancellation\Actions\DeleteBulkCancellationAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('BulkCancellations')]
class BulkCancellations extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $barber_shop_id = '';
    #[Url(history: true)] public $barber_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'barber_shop_id', 'barber_id', ]); $this->resetPage(); }

    #[On('echo-private:mobile.bulk-cancellations,.bulk-cancellations.changed')]
    public function onBroadcastCloudChange($event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_bulk_cancellations');
        $query = (new BulkCancellationListQuery())->handle(['search' => $this->search,             'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.bulk-cancellations.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => BulkCancellation::sortable(),
            'barberShops' => \App\Models\BarberShop::pluck('name', 'id')->toArray(),
            'barbers' => \App\Models\Barber::pluck('name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, BulkCancellation::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteBulkCancellation($id, DeleteBulkCancellationAction $action) 
    {
        abort_if_cannot('delete_bulk_cancellations');
        $item = BulkCancellation::find($id);
        if (!$item) { $this->dispatch('toast', message: __('bulk-cancellations.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('bulk-cancellations.deleted'), type: 'success'); $this->resetPage(); } 
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('bulk-cancellations.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('bulk-cancellations.delete_error'), type: 'error'); }
    }
}