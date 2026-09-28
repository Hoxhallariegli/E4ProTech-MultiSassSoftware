<?php

namespace App\Livewire\Admin\Plans;

use App\Models\Plan;
use App\Domain\Plan\Queries\PlanListQuery;
use App\Domain\Plan\Actions\DeletePlanAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Plans')]
class Plans extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', ]); $this->resetPage(); }

    #[On('echo-private:mobile.plans,.plans.changed')]
    public function onBroadcastCloudChange($event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_plans');
        $query = (new PlanListQuery())->handle(['search' => $this->search, ], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.plans.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => Plan::sortable(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, Plan::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deletePlan($id, DeletePlanAction $action) 
    {
        abort_if_cannot('delete_plans');
        $item = Plan::find($id);
        if (!$item) { $this->dispatch('toast', message: __('plans.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('plans.deleted'), type: 'success'); $this->resetPage(); } 
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('plans.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('plans.delete_error'), type: 'error'); }
    }
}