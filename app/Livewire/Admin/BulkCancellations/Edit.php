<?php

namespace App\Livewire\Admin\BulkCancellations;

use App\Models\BulkCancellation;
use App\Domain\BulkCancellation\DTOs\BulkCancellationDTO;
use App\Domain\BulkCancellation\Actions\UpdateBulkCancellationAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit BulkCancellation')]
class Edit extends Component
{
        use WithPagination;
 public BulkCancellation $item;
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

    public function mount(BulkCancellation $bulkCancellation) { $this->item = $bulkCancellation; $this->fill($bulkCancellation->toArray()); $this->start_at = $bulkCancellation->start_at?->format('Y-m-d\TH:i'); $this->end_at = $bulkCancellation->end_at?->format('Y-m-d\TH:i'); }
    public function render() { abort_if_cannot('edit_bulk_cancellations'); return view('livewire.admin.bulk-cancellations.edit', [
            'barberShops' => $this->getbarberShopsList(),
            'barbers' => $this->getbarbersList(),
        ])->layout('components.layouts.app'); }
    public function update(UpdateBulkCancellationAction $action) { $this->validate();  $dto = BulkCancellationDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'reason' => $this->reason,
            'affected_count' => $this->affected_count,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('bulk-cancellations.updated')); return to_route('admin.bulk-cancellations.index'); }
    protected function rules(): array { $rules = BulkCancellation::rules($this->item->id);
        return $rules; }
}