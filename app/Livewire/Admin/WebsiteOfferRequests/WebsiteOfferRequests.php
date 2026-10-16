<?php

namespace App\Livewire\Admin\WebsiteOfferRequests;

use App\Models\WebsiteOfferRequest;
use App\Domain\WebsiteOfferRequest\Queries\WebsiteOfferRequestListQuery;
use App\Domain\WebsiteOfferRequest\Actions\DeleteWebsiteOfferRequestAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Title('Kërkesat për Uebsajt')]
class WebsiteOfferRequests extends Component
{
    use WithPagination;

    public int $paginate = 15;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public string $status = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = false;

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'openFilter']);
        $this->resetPage();
    }

    public function render()
    {
        $query = (new WebsiteOfferRequestListQuery())->handle([
            'search' => $this->search,
            'status' => $this->status,
        ], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.website-offer-requests.index', [
            'items' => $query->paginate($this->paginate),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        }
        $this->sortField = $field;
    }

    public function deleteRequest($id, DeleteWebsiteOfferRequestAction $action)
    {
        $item = WebsiteOfferRequest::find($id);
        if (!$item) {
            $this->dispatch('toast', message: 'Kërkesa nuk u gjet.', type: 'error');
            return;
        }

        try {
            $action->execute($item);
            $this->dispatch('toast', message: 'Kërkesa u fshi me sukses.', type: 'success');
            $this->resetPage();
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Gabim gjatë fshirjes: ' . $e->getMessage(), type: 'error');
        }
    }
}
