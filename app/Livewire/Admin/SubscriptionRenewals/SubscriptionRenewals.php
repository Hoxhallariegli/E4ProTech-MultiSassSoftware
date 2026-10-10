<?php

namespace App\Livewire\Admin\SubscriptionRenewals;

use App\Models\SubscriptionRenewal;
use App\Models\Plan;
use App\Domain\SubscriptionRenewal\Queries\SubscriptionRenewalListQuery;
use App\Domain\SubscriptionRenewal\Actions\DeleteSubscriptionRenewalAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

#[Title('Renovimi i Abonimeve')]
class SubscriptionRenewals extends Component
{
    use WithPagination, WithFileUploads;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $plan_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = false;

    // Pricing & Duration Toggles: 6 months, 12 months (10% OFF), 24 months (20% OFF)
    public int $billingDuration = 6;

    public function setBillingDuration(int $months)
    {
        if (in_array($months, [6, 12, 24], true)) {
            $this->billingDuration = $months;
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'openFilter', 'plan_id']);
        $this->resetPage();
    }

    #[On('echo-private:mobile.subscription-renewals,.subscription-renewals.changed')]
    public function onBroadcastCloudChange($event)
    {
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_subscription_renewals');

        $query = (new SubscriptionRenewalListQuery())->handle([
            'search' => $this->search,
            'plan_id' => $this->plan_id,
        ], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        $availablePlans = Plan::where('active', true)
            ->where('name', 'not like', '%trial%')
            ->where('name', 'not like', '%prova%')
            ->get();

        return view('livewire.admin.subscription-renewals.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => SubscriptionRenewal::sortable(),
            'plans' => Plan::where('name', 'not like', '%trial%')->pluck('name', 'id')->toArray(),
            'availablePlans' => $availablePlans,
        ])->layout('components.layouts.app');
    }

    public function sortBy($field)
    {
        if (!in_array($field, SubscriptionRenewal::sortable(), true)) return;
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        }
        $this->sortField = $field;
    }

    public function deleteSubscriptionRenewal($id, DeleteSubscriptionRenewalAction $action)
    {
        abort_if_cannot('delete_subscription_renewals');
        $item = SubscriptionRenewal::find($id);
        if (!$item) {
            $this->dispatch('toast', message: __('subscription-renewals.not_found'), type: 'error');
            return;
        }
        try {
            $action->execute($item);
            $this->dispatch('toast', message: __('subscription-renewals.deleted'), type: 'success');
            $this->resetPage();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->dispatch('toast', message: __('subscription-renewals.delete_error_referenced'), type: 'error');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: __('subscription-renewals.delete_error'), type: 'error');
        }
    }
}
