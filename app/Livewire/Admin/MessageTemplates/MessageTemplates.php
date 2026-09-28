<?php

namespace App\Livewire\Admin\MessageTemplates;

use App\Models\MessageTemplate;
use App\Domain\MessageTemplate\Queries\MessageTemplateListQuery;
use App\Domain\MessageTemplate\Actions\DeleteMessageTemplateAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('MessageTemplates')]
class MessageTemplates extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $barber_shop_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'barber_shop_id', ]); $this->resetPage(); }

    #[On('echo-private:mobile.message-templates,.message-templates.changed')]
    public function onBroadcastCloudChange($event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_message_templates');
        $query = (new MessageTemplateListQuery())->handle(['search' => $this->search,             'barber_shop_id' => $this->barber_shop_id,
], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.message-templates.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => MessageTemplate::sortable(),
            'barberShops' => \App\Models\BarberShop::pluck('name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, MessageTemplate::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteMessageTemplate($id, DeleteMessageTemplateAction $action) 
    {
        abort_if_cannot('delete_message_templates');
        $item = MessageTemplate::find($id);
        if (!$item) { $this->dispatch('toast', message: __('message-templates.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('message-templates.deleted'), type: 'success'); $this->resetPage(); } 
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('message-templates.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('message-templates.delete_error'), type: 'error'); }
    }
}