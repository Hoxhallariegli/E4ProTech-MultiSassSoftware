<?php

namespace App\Livewire\Admin\Barbers;

use App\Models\Barber;
use App\Domain\Barber\Queries\BarberListQuery;
use App\Domain\Barber\Actions\DeleteBarberAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

#[Title('Barbers')]
class Barbers extends Component
{
        use WithPagination, WithFileUploads;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $barber_shop_id = '';
    #[Url(history: true)] public $user_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'barber_shop_id', 'user_id', ]); $this->resetPage(); }

    #[On('echo-private:mobile.barbers,.barbers.changed')]
    public function onBroadcastCloudChange($event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_barbers');
        $query = (new BarberListQuery())->handle(['search' => $this->search,             'barber_shop_id' => $this->barber_shop_id,
            'user_id' => $this->user_id,
], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.barbers.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => Barber::sortable(),
            'barberShops' => \App\Models\BarberShop::pluck('name', 'id')->toArray(),
            'users' => \App\Models\User::pluck('name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, Barber::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteBarber($id, DeleteBarberAction $action) 
    {
        abort_if_cannot('delete_barbers');
        $item = Barber::find($id);
        if (!$item) { $this->dispatch('toast', message: __('barbers.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('barbers.deleted'), type: 'success'); $this->resetPage(); } 
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('barbers.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('barbers.delete_error'), type: 'error'); }
    }
}