<?php

namespace App\Livewire\Admin\Services;

use App\Models\Service;
use App\Domain\Service\DTOs\ServiceDTO;
use App\Domain\Service\Actions\CreateServiceAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class QuickCreate extends Component
{
        use WithPagination;
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

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.services.quick-create', [
            'barberShops' => $this->getbarberShopsList(),
        ]); }

    public function store(CreateServiceAction $action)
    {
        $this->validate();
        $dto = ServiceDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'category' => $this->category,
            'active' => $this->active,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('service-created', id: $item->id);
        $this->js("Livewire.dispatch('service-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('services.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->name ?? $item->id);
        $this->reset(['barber_shop_id', 'name', 'description', 'price', 'duration_minutes', 'category', 'active']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = Service::rules();
        return $rules; }
}