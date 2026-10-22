<?php

namespace App\Livewire\Admin\BulkCancellations;

use App\Models\BulkCancellation;
use App\Domain\BulkCancellation\DTOs\BulkCancellationDTO;
use App\Domain\BulkCancellation\Actions\CreateBulkCancellationAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class QuickCreate extends Component
{
        use WithPagination;
     public $barber_shop_id = '';
    public $barber_id = '';
    public $start_at = '';
    public $end_at = '';
    public $reason = '';
    public $affected_count = '';
 
    #[On('barber-shop-created')] 
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }

    #[On('barber-created')] 
    public function refreshBarbers($id) { $this->barber_id = $id; $this->updatedBarberId($id); }
 
    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
        if (isset($related->barber_id)) { $this->barber_id = $related->barber_id; }
    }

    public function updatedBarberId($value)
    {
        if (!$value) return;
        $related = \App\Models\Barber::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id)) { $this->barber_shop_id = $related->barber_shop_id; }
    }
 
    protected function getbarberShopsList() {
        $query = \App\Models\BarberShop::query();
        if (method_exists(\App\Models\BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    protected function getbarbersList() {
        $query = \App\Models\Barber::query();
        if (method_exists(\App\Models\Barber::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.bulk-cancellations.quick-create', [
            'barberShops' => $this->getbarberShopsList(),
            'barbers' => $this->getbarbersList(),
        ]); }

    public function store(CreateBulkCancellationAction $action)
    {
        $this->validate();
        $dto = BulkCancellationDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'reason' => $this->reason,
            'affected_count' => $this->affected_count,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('bulk-cancellation-created', id: $item->id);
        $this->js("Livewire.dispatch('bulk-cancellation-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('bulk-cancellations.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['barber_shop_id', 'barber_id', 'start_at', 'end_at', 'reason', 'affected_count']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = BulkCancellation::rules();
        return $rules; }
}