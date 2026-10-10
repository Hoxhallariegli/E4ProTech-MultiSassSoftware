<?php

namespace App\Livewire\Admin\SubscriptionRenewals;

use App\Models\SubscriptionRenewal;
use App\Domain\SubscriptionRenewal\DTOs\SubscriptionRenewalDTO;
use App\Domain\SubscriptionRenewal\Actions\CreateSubscriptionRenewalAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

class QuickCreate extends Component
{
        use WithPagination, WithFileUploads;
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

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.subscription-renewals.quick-create', [
            'plans' => $this->getplansList(),
        ]); }

    public function store(CreateSubscriptionRenewalAction $action)
    {
        $this->validate();
        if ($this->transfer_document && !is_string($this->transfer_document)) { $this->transfer_document = app(\App\Services\ImageUploadService::class)->upload($this->transfer_document, 'uploads/subscription-renewals'); }
        $dto = SubscriptionRenewalDTO::fromArray([
            'plan_id' => $this->plan_id,
            'payment_method' => $this->payment_method,
            'transfer_document' => $this->transfer_document,
            'amount' => $this->amount,
            'notes' => $this->notes,
            'status' => $this->status,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('subscription-renewal-created', id: $item->id);
        $this->js("Livewire.dispatch('subscription-renewal-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('subscription-renewals.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['plan_id', 'payment_method', 'transfer_document', 'amount', 'notes', 'status']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = SubscriptionRenewal::rules();
        if ($this->transfer_document instanceof \Illuminate\Http\UploadedFile) { $rules['transfer_document'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->transfer_document) && $this->transfer_document !== '') { $rules['transfer_document'] = ['nullable', 'string', 'max:255']; } else { $rules['transfer_document'] = ['nullable']; }
        return $rules; }
}
