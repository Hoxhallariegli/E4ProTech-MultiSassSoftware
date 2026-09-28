<?php

namespace App\Livewire\Admin\Subscriptions;

use App\Models\Subscription;
use App\Domain\Subscription\Queries\SubscriptionListQuery;
use App\Domain\Subscription\Actions\DeleteSubscriptionAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Subscriptions')]
class Subscriptions extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $barber_shop_id = '';
    #[Url(history: true)] public $plan_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'barber_shop_id', 'plan_id', ]); $this->resetPage(); }

    #[On('echo-private:mobile.subscriptions,.subscriptions.changed')]
    public function onBroadcastCloudChange($event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_subscriptions');
        $query = (new SubscriptionListQuery())->handle(['search' => $this->search,             'barber_shop_id' => $this->barber_shop_id,
            'plan_id' => $this->plan_id,
], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.subscriptions.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => Subscription::sortable(),
            'barberShops' => \App\Models\BarberShop::pluck('name', 'id')->toArray(),
            'plans' => \App\Models\Plan::pluck('name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, Subscription::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteSubscription($id, DeleteSubscriptionAction $action) 
    {
        abort_if_cannot('delete_subscriptions');
        $item = Subscription::find($id);
        if (!$item) { $this->dispatch('toast', message: __('subscriptions.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('subscriptions.deleted'), type: 'success'); $this->resetPage(); } 
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('subscriptions.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('subscriptions.delete_error'), type: 'error'); }
    }
}