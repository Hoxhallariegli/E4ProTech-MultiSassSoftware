<?php

namespace App\Livewire\Admin\Services;

use App\Models\Service;
use App\Domain\Service\DTOs\ServiceDTO;
use App\Domain\Service\Actions\UpdateServiceAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit Service')]
class Edit extends Component
{
        use WithPagination;
 public Service $item;
    public $barber_shop_id = '';
    public $name = '';
    public $description = '';
    public $price = '';
    public $duration_minutes = '';
    public $category = '';
    public $active = false;
 
    #[On('barber-shop-created')] 
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }
 
    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
    }
 
    protected function getbarberShopsList() {
        $query = \App\Models\BarberShop::query();
        if (method_exists(\App\Models\BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    public function mount(Service $service) { $this->item = $service; $this->fill($service->toArray());  }
    public function render() { abort_if_cannot('edit_services'); return view('livewire.admin.services.edit', [
            'barberShops' => $this->getbarberShopsList(),
        ])->layout('components.layouts.app'); }
    public function update(UpdateServiceAction $action) { $this->validate();  $dto = ServiceDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'category' => $this->category,
            'active' => $this->active,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('services.updated')); return to_route('admin.services.index'); }
    protected function rules(): array { $rules = Service::rules($this->item->id);
        return $rules; }
}