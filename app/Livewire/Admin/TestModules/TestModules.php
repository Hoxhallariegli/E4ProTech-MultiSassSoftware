<?php

namespace App\Livewire\Admin\TestModules;

use App\Models\TestModule;
use App\Domain\TestModule\Queries\TestModuleListQuery;
use App\Domain\TestModule\Actions\DeleteTestModuleAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

#[Title('TestModules')]
class TestModules extends Component
{
        use WithPagination, WithFileUploads;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $user_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'user_id', ]); $this->resetPage(); }

    #[On('echo-private:mobile.test-modules,.test-modules.changed')]
    public function onBroadcastCloudChange($event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_test_modules');
        $query = (new TestModuleListQuery())->handle(['search' => $this->search,             'user_id' => $this->user_id,
], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.test-modules.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => TestModule::sortable(),
            'users' => \App\Models\User::pluck('name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, TestModule::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteTestModule($id, DeleteTestModuleAction $action) 
    {
        abort_if_cannot('delete_test_modules');
        $item = TestModule::find($id);
        if (!$item) { $this->dispatch('toast', message: __('test-modules.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('test-modules.deleted'), type: 'success'); $this->resetPage(); } 
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('test-modules.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('test-modules.delete_error'), type: 'error'); }
    }
}