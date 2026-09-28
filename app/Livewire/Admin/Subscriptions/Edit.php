<?php

namespace App\Livewire\Admin\Subscriptions;

use App\Models\Subscription;
use App\Domain\Subscription\DTOs\SubscriptionDTO;
use App\Domain\Subscription\Actions\UpdateSubscriptionAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit Subscription')]
class Edit extends Component
{
        use WithPagination;
 public Subscription $item;
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

    public function mount(Subscription $subscription) { $this->item = $subscription; $this->fill($subscription->toArray()); $this->starts_at = $subscription->starts_at?->format('Y-m-d\TH:i'); $this->ends_at = $subscription->ends_at?->format('Y-m-d\TH:i'); }
    public function render() { abort_if_cannot('edit_subscriptions'); return view('livewire.admin.subscriptions.edit', [
            'barberShops' => $this->getbarberShopsList(),
            'plans' => $this->getplansList(),
        ])->layout('components.layouts.app'); }
    public function update(UpdateSubscriptionAction $action) { $this->validate();  $dto = SubscriptionDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'plan_id' => $this->plan_id,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'status' => $this->status,
            'auto_renew' => $this->auto_renew,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('subscriptions.updated')); return to_route('admin.subscriptions.index'); }
    protected function rules(): array { $rules = Subscription::rules($this->item->id);
        return $rules; }
}