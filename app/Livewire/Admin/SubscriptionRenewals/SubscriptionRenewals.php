<?php

namespace App\Livewire\Admin\SubscriptionRenewals;

use App\Models\SubscriptionRenewal;
use App\Models\Plan;
use App\Models\WebsiteOfferRequest;
use App\Models\AuditTrail;
use App\Notifications\WebsiteOfferRequestedNotification;
use App\Domain\SubscriptionRenewal\Queries\SubscriptionRenewalListQuery;
use App\Domain\SubscriptionRenewal\Actions\DeleteSubscriptionRenewalAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

#[Title('Renovimi i Abonimeve')]
class SubscriptionRenewals extends Component
{
    use WithPagination, WithFileUploads;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $plan_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = false;

    // Pricing & Duration Toggles: 6 months, 12 months (5% OFF), 24 months (10% OFF)
    public int $billingDuration = 6;

    // Website Offer Request Modal Properties
    public bool $showOfferModal = false;
    public string $desired_domain = '';
    public string $offer_phone = '';
    public string $offer_notes = '';

    public function setBillingDuration(int $months)
    {
        if (in_array($months, [6, 12, 24], true)) {
            $this->billingDuration = $months;
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'openFilter', 'plan_id']);
        $this->resetPage();
    }

    public function openOfferModal()
    {
        $user = auth()->user();
        $shop = $user?->barberShop;

        $this->desired_domain = $shop ? 'www.' . $shop->slug . '.al' : '';
        $this->offer_phone = $shop?->phone ?? '';
        $this->offer_notes = '';
        $this->showOfferModal = true;
    }

    public function closeOfferModal()
    {
        $this->showOfferModal = false;
        $this->reset(['desired_domain', 'offer_phone', 'offer_notes']);
    }

    public function submitWebsiteOffer()
    {
        $this->validate([
            'desired_domain' => 'required|string|max:100',
            'offer_phone' => 'required|string|max:30',
            'offer_notes' => 'nullable|string|max:1000',
        ], [
            'desired_domain.required' => 'Ju lutemi shkruani domain-in e dëshiruar (p.sh. www.sallonituaj.al).',
            'offer_phone.required' => 'Ju lutemi shkruani numrin e telefonit për kontakt.',
        ]);

        $user = auth()->user();
        $shop = $user?->barberShop ?: \App\Models\BarberShop::first();

        if (!$shop) {
            $this->dispatch('toast', message: 'Nuk u gjet asnjë sallon aktiv.', type: 'error');
            return;
        }

        $offerRequest = WebsiteOfferRequest::create([
            'barber_shop_id' => $shop->id,
            'user_id' => $user->id,
            'desired_domain' => trim($this->desired_domain),
            'phone' => trim($this->offer_phone),
            'notes' => trim($this->offer_notes),
            'status' => 'pending',
        ]);

        // Audit Trail Log
        AuditTrail::logCustom(
            $offerRequest,
            'create',
            'Subscriptions',
            "Kërkesë për Uebsajt të Personalizuar ({$offerRequest->desired_domain})"
        );

        // Send Email Notification to Super Admin
        try {
            $adminUser = \App\Models\User::where('email', 'admin@e4protech.com')
                ->orWhere('email', 'hoxhallariegli@gmail.com')
                ->first();

            if ($adminUser) {
                $adminUser->notify(new WebsiteOfferRequestedNotification($offerRequest));
            } else {
                \Illuminate\Support\Facades\Notification::route('mail', 'admin@e4protech.com')
                    ->notify(new WebsiteOfferRequestedNotification($offerRequest));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Website offer notification email warning: " . $e->getMessage());
        }

        $this->closeOfferModal();
        $this->dispatch('toast', message: 'Kërkesa juaj për Uebsajt të Personalizuar u dërgua me sukses! Ekipi ynë do t\'ju kontaktojë së shpejti. 🚀', type: 'success');
    }

    #[On('echo-private:mobile.subscription-renewals,.subscription-renewals.changed')]
    public function onBroadcastCloudChange($event)
    {
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_subscription_renewals');

        $query = (new SubscriptionRenewalListQuery())->handle([
            'search' => $this->search,
            'plan_id' => $this->plan_id,
        ], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        $availablePlans = Plan::where('active', true)
            ->where('name', 'not like', '%trial%')
            ->where('name', 'not like', '%prova%')
            ->get();

        return view('livewire.admin.subscription-renewals.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => SubscriptionRenewal::sortable(),
            'plans' => Plan::where('name', 'not like', '%trial%')->pluck('name', 'id')->toArray(),
            'availablePlans' => $availablePlans,
        ])->layout('components.layouts.app');
    }

    public function sortBy($field)
    {
        if (!in_array($field, SubscriptionRenewal::sortable(), true)) return;
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        }
        $this->sortField = $field;
    }

    public function deleteSubscriptionRenewal($id, DeleteSubscriptionRenewalAction $action)
    {
        abort_if_cannot('delete_subscription_renewals');
        $item = SubscriptionRenewal::find($id);
        if (!$item) {
            $this->dispatch('toast', message: __('subscription-renewals.not_found'), type: 'error');
            return;
        }
        try {
            $action->execute($item);
            $this->dispatch('toast', message: __('subscription-renewals.deleted'), type: 'success');
            $this->resetPage();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->dispatch('toast', message: __('subscription-renewals.delete_error_referenced'), type: 'error');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: __('subscription-renewals.delete_error'), type: 'error');
        }
    }
}
