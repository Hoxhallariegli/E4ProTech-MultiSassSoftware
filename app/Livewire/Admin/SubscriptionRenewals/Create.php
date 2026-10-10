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

#[Title('Add SubscriptionRenewal')]
class Create extends Component
{
    use WithPagination, WithFileUploads;

    public $plan_id = '';
    public $payment_method = 'bank_transfer';
    public $transfer_document = '';
    public $amount = '';
    public $notes = '';
    public $status = 'pending';

    public function mount()
    {
        $planIdParam = request()->query('plan_id');
        $amountParam = request()->query('amount');
        $durationParam = request()->query('duration');

        if ($planIdParam) {
            $this->plan_id = $planIdParam;
        }
        if ($amountParam) {
            $this->amount = number_format((float) $amountParam, 2, '.', '');
        }
        if ($durationParam) {
            $this->notes = "Kërkesë për abonim {$durationParam} Muaj.";
        }
        $this->payment_method = 'bank_transfer';
        $this->status = 'pending';
    }

    #[On('plan-created')]
    public function refreshPlans($id)
    {
        $this->plan_id = $id;
        $this->updatedPlanId($id);
    }

    public function updatedPlanId($value)
    {
        if (!$value) return;
        $related = \App\Models\Plan::find($value);
        if (!$related) return;
        if (empty($this->amount) || $this->amount == '0' || $this->amount == '0.00') {
            $this->amount = number_format((float) ($related->price ?? 0), 2, '.', '');
        }
    }

    protected function getplansList()
    {
        $query = \App\Models\Plan::query()
            ->where('name', 'not like', '%trial%')
            ->where('name', 'not like', '%prova%');
        if (method_exists(\App\Models\Plan::class, 'scopeForActiveShop')) {
            $query->forActiveShop();
        }
        return $query->pluck('name', 'id')->toArray();
    }

    public function render()
    {
        abort_if_cannot('add_subscription_renewals');
        return view('livewire.admin.subscription-renewals.create', [
            'plans' => $this->getplansList(),
        ])->layout('components.layouts.app');
    }

    public function store(CreateSubscriptionRenewalAction $action)
    {
        $this->validate();
        if ($this->transfer_document && !is_string($this->transfer_document)) {
            $this->transfer_document = app(\App\Services\ImageUploadService::class)->upload($this->transfer_document, 'uploads/subscription-renewals');
        }
        $dto = SubscriptionRenewalDTO::fromArray([
            'plan_id' => $this->plan_id,
            'payment_method' => $this->payment_method,
            'transfer_document' => $this->transfer_document,
            'amount' => $this->amount,
            'notes' => $this->notes,
            'status' => $this->status,
        ]);
        $action->execute($dto);
        session()->flash('success', __('subscription-renewals.created'));
        return to_route('admin.subscription-renewals.index');
    }

    protected function rules(): array
    {
        $rules = SubscriptionRenewal::rules();
        if ($this->transfer_document instanceof \Illuminate\Http\UploadedFile) {
            $rules['transfer_document'] = ['nullable', 'image', 'max:10240'];
        } elseif (is_string($this->transfer_document) && $this->transfer_document !== '') {
            $rules['transfer_document'] = ['nullable', 'string', 'max:255'];
        } else {
            $rules['transfer_document'] = ['nullable'];
        }
        return $rules;
    }
}
