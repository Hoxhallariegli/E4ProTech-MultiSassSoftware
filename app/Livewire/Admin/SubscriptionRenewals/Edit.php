<?php

namespace App\Livewire\Admin\SubscriptionRenewals;

use App\Models\SubscriptionRenewal;
use App\Domain\SubscriptionRenewal\DTOs\SubscriptionRenewalDTO;
use App\Domain\SubscriptionRenewal\Actions\UpdateSubscriptionRenewalAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

#[Title('Edit SubscriptionRenewal')]
class Edit extends Component
{
        use WithPagination, WithFileUploads;
 public SubscriptionRenewal $item;
    public $plan_id = '';
    public $payment_method = '';
    public $transfer_document = '';
    public $amount = '';
    public $notes = '';
    public $status = '';

    #[On('plan-created')]
    public function refreshPlans($id) { $this->plan_id = $id; $this->updatedPlanId($id); }

    public function updatedPlanId($value)
    {
        if (!$value) return;
        $related = \App\Models\Plan::find($value);
        if (!$related) return;
    }

    protected function getplansList() {
        $query = \App\Models\Plan::query()
            ->where('name', 'not like', '%trial%')
            ->where('name', 'not like', '%prova%');
        if (method_exists(\App\Models\Plan::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    public function mount(SubscriptionRenewal $subscriptionRenewal) { $this->item = $subscriptionRenewal; $this->fill($subscriptionRenewal->toArray());  }
    public function render() { abort_if_cannot('edit_subscription_renewals'); return view('livewire.admin.subscription-renewals.edit', [
            'plans' => $this->getplansList(),
        ])->layout('components.layouts.app'); }
    public function update(UpdateSubscriptionRenewalAction $action) { $this->validate();         if ($this->transfer_document && !is_string($this->transfer_document)) { $this->transfer_document = app(\App\Services\ImageUploadService::class)->upload($this->transfer_document, 'uploads/subscription-renewals'); }
 $dto = SubscriptionRenewalDTO::fromArray([
            'plan_id' => $this->plan_id,
            'payment_method' => $this->payment_method,
            'transfer_document' => $this->transfer_document,
            'amount' => $this->amount,
            'notes' => $this->notes,
            'status' => $this->status,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('subscription-renewals.updated')); return to_route('admin.subscription-renewals.index'); }
    protected function rules(): array { $rules = SubscriptionRenewal::rules($this->item->id);
        if ($this->transfer_document instanceof \Illuminate\Http\UploadedFile) { $rules['transfer_document'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->transfer_document) && $this->transfer_document !== '') { $rules['transfer_document'] = ['nullable', 'string', 'max:255']; } else { $rules['transfer_document'] = ['nullable']; }
        return $rules; }
}
