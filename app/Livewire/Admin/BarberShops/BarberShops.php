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
    public bool $sortAsc = false;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'owner_id']); $this->resetPage(); }

    #[On('echo-private:mobile.barber-shops,.barber-shops.changed')]
    public function onBroadcastCloudChange($event)
    {
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_barber_shops');
        $query = (new BarberShopListQuery())->handle([
            'search' => $this->search,
            'owner_id' => $this->owner_id,
        ], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        $owners = [];
        if (auth()->check() && auth()->user()->is_global_admin) {
            $owners = \App\Models\User::scopeForActiveShop()->pluck('name', 'id')->toArray();
        }

        return view('livewire.admin.barber-shops.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => BarberShop::sortable(),
            'owners' => $owners,
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, BarberShop::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteBarberShop($id, DeleteBarberShopAction $action)
    {
        abort_if_cannot('delete_barber_shops');
        $item = BarberShop::find($id);
        if (!$item) {
            $this->dispatch('toast', message: __('barber-shops.not_found'), type: 'error');
            return;
        }

        if (auth()->check() && !auth()->user()->is_global_admin) {
            $user = auth()->user();
            $userShopIdsFromRoles = \Illuminate\Support\Facades\DB::table('model_has_roles')
                ->where('model_id', $user->id)
                ->where('barber_shop_id', '>', 0)
                ->pluck('barber_shop_id')
                ->toArray();

            $isAllowed = ($item->owner_id == $user->id)
                || ($item->id == $user->barber_shop_id)
                || in_array($item->id, $userShopIdsFromRoles)
                || $item->users()->where('users.id', $user->id)->exists();

            abort_if(!$isAllowed, 403);
        }

        try {
            $action->execute($item);
            $this->dispatch('toast', message: __('barber-shops.deleted'), type: 'success');
            $this->resetPage();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Delete shop error: " . $e->getMessage());
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }
}
