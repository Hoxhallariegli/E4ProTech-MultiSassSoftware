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

#[Title('Add Service')]
class Create extends Component
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
 
    public function mount() { if (auth()->check() && auth()->user()->barber_shop_id) { $this->barber_shop_id = auth()->user()->barber_shop_id; } }

    public function render() { abort_if_cannot('add_services');         $shop = \App\Models\BarberShop::find($this->barber_shop_id);
        $canAdd = true;
        if ($shop && !auth()->user()->hasRole(['admin', 'qqq'])) {
            $canAdd = app(\App\Services\SubscriptionService::class)->canAddService($shop);
        }
        return view('livewire.admin.services.create', [
            'limitReached' => !$canAdd,
            'barberShops' => $this->getbarberShopsList(),
        ])->layout('components.layouts.app'); }
    public function store(CreateServiceAction $action) { $this->validate();  $dto = ServiceDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
            'category' => $this->category,
            'active' => $this->active,
        ]);         $shop = \App\Models\BarberShop::find($this->barber_shop_id);
        if ($shop && !auth()->user()->hasRole(['admin', 'qqq'])) {
            if (!app(\App\Services\SubscriptionService::class)->canAddService($shop)) {
                session()->flash('error', __('Limit reached for this plan.'));
                return;
            }
        }
        $action->execute($dto); session()->flash('success', __('services.created')); return to_route('admin.services.index'); }
    protected function rules(): array { $rules = Service::rules();
        return $rules; }
}