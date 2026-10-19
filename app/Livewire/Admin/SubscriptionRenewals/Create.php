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

    public $barber_shop_id = '';
    public $plan_id = '';
    public $duration = 6;
    public $payment_method = 'cash';
    public $transfer_document = '';
    public $amount = '0.00';
    public $notes = '';
    public $status = 'pending';

    public function mount()
    {
        if (auth()->check() && auth()->user()->barber_shop_id) {
            $this->barber_shop_id = (string) auth()->user()->barber_shop_id;
        }

        $planIdParam = request()->query('plan_id');
        $amountParam = request()->query('amount');
        $durationParam = request()->query('duration');

        if ($durationParam) {
            $this->duration = max(1, (int) $durationParam);
        }

        if ($planIdParam) {
            $this->plan_id = (string) $planIdParam;
            $this->updatedPlanId($this->plan_id);
        }

        if ($amountParam && (empty($this->amount) || $this->amount == '0.00')) {
            $this->amount = number_format((float) $amountParam, 2, '.', '');
        }

        $this->payment_method = 'cash';
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
        if (!$value) {
            $this->amount = '0.00';
            return;
        }
        $plan = \App\Models\Plan::find($value);
        if (!$plan) return;

        $basePrice = (float) ($plan->price ?? 0);
        $months = (int) ($this->duration ?: 6);
        $discountPct = $months === 12 ? 0.05 : ($months === 24 ? 0.10 : 0.0);
        $totalPrice = ($basePrice * $months) * (1.0 - $discountPct);

        $this->amount = number_format($totalPrice, 2, '.', '');
        $this->notes = "Kërkesë për abonim {$months} Muaj për planin {$plan->name}.";
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
            'barber_shop_id' => $this->barber_shop_id ?: auth()->user()->barber_shop_id,
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
        if (!auth()->user()->is_global_admin) {
            unset($rules['barber_shop_id']);
        } else {
            $rules['barber_shop_id'] = ['nullable'];
        }

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
