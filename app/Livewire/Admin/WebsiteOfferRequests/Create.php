<?php

namespace App\Livewire\Admin\WebsiteOfferRequests;

use App\Models\WebsiteOfferRequest;
use App\Models\BarberShop;
use App\Domain\WebsiteOfferRequest\DTOs\WebsiteOfferRequestDTO;
use App\Domain\WebsiteOfferRequest\Actions\CreateWebsiteOfferRequestAction;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Kërkesë e Re për Uebsajt')]
class Create extends Component
{
    public $barber_shop_id = '';
    public $desired_domain = '';
    public $phone = '';
    public $notes = '';
    public $status = 'pending';

    public function mount()
    {
        $shop = auth()->user()?->barberShop ?: BarberShop::first();
        if ($shop) {
            $this->barber_shop_id = $shop->id;
            $this->desired_domain = 'www.' . $shop->slug . '.al';
            $this->phone = $shop->phone ?? '';
        }
    }

    public function render()
    {
        return view('livewire.admin.website-offer-requests.create', [
            'shops' => BarberShop::pluck('name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function store(CreateWebsiteOfferRequestAction $action)
    {
        $this->validate([
            'barber_shop_id' => 'required|exists:barber_shops,id',
            'desired_domain' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|string|in:pending,contacted,completed',
        ], [
            'desired_domain.required' => 'Ju lutemi shkruani domain-in e dëshiruar.',
            'phone.required' => 'Ju lutemi shkruani numrin e telefonit.',
        ]);

        $dto = WebsiteOfferRequestDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'user_id' => auth()->id(),
            'desired_domain' => trim($this->desired_domain),
            'phone' => trim($this->phone),
            'notes' => trim($this->notes),
            'status' => $this->status,
        ]);

        $action->execute($dto);

        session()->flash('success', 'Kërkesa për uebsajt u krijua me sukses.');
        return to_route('admin.website-offer-requests.index');
    }
}
