<?php

namespace App\Livewire\Admin\Subscriptions;

use App\Models\Subscription;
use App\Domain\Subscription\DTOs\SubscriptionDTO;
use App\Domain\Subscription\Actions\CreateSubscriptionAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class QuickCreate extends Component
{
        use WithPagination;
     public $barber_shop_id = '';
    public $plan_id = '';
    public $starts_at = '';
    public $ends_at = '';
    public $status = '';
    public $auto_renew = false;
 
    #[On('barber-shop-created')] 
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }

    #[On('plan-created')] 
    public function refreshPlans($id) { $this->plan_id = $id; $this->updatedPlanId($id); }
 
    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
        if (isset($related->plan_id)) { $this->plan_id = $related->plan_id; }
    }

    public function updatedPlanId($value)
    {
        if (!$value) return;
        $related = \App\Models\Plan::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id)) { $this->barber_shop_id = $related->barber_shop_id; }
    }
 
    protected function getbarberShopsList() {
        $query = \App\Models\BarberShop::query();
        if (method_exists(\App\Models\BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    protected function getplansList() {
        $query = \App\Models\Plan::query();
        if (method_exists(\App\Models\Plan::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.subscriptions.quick-create', [
            'barberShops' => $this->getbarberShopsList(),
            'plans' => $this->getplansList(),
        ]); }

    public function store(CreateSubscriptionAction $action)
    {
        $this->validate();
        $dto = SubscriptionDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'plan_id' => $this->plan_id,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'status' => $this->status,
            'auto_renew' => $this->auto_renew,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('subscription-created', id: $item->id);
        $this->js("Livewire.dispatch('subscription-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('subscriptions.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['barber_shop_id', 'plan_id', 'starts_at', 'ends_at', 'status', 'auto_renew']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = Subscription::rules();
        return $rules; }
}