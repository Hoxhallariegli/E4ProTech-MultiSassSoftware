<?php

namespace App\Livewire\Admin\WebsiteOfferRequests;

use App\Models\WebsiteOfferRequest;
use App\Models\BarberShop;
use App\Domain\WebsiteOfferRequest\DTOs\WebsiteOfferRequestDTO;
use App\Domain\WebsiteOfferRequest\Actions\CreateWebsiteOfferRequestAction;
use Livewire\Component;

class QuickCreate extends Component
{
    public $barber_shop_id = '';
    public $desired_domain = '';
    public $phone = '';
    public $notes = '';
    public $status = 'pending';

    public bool $created = false;
    public string $createdLabel = '';

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
        return view('livewire.admin.website-offer-requests.quick-create', [
            'shops' => BarberShop::pluck('name', 'id')->toArray(),
        ]);
    }

    public function store(CreateWebsiteOfferRequestAction $action)
    {
        $this->validate([
            'barber_shop_id' => 'required|exists:barber_shops,id',
            'desired_domain' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'notes' => 'nullable|string|max:1000',
        ], [
            'desired_domain.required' => 'Ju lutemi shkruani domain-in e dëshiruar (p.sh. www.sallonituaj.al).',
            'phone.required' => 'Ju lutemi shkruani numrin e telefonit për kontakt.',
        ]);

        $dto = WebsiteOfferRequestDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'user_id' => auth()->id(),
            'desired_domain' => trim($this->desired_domain),
            'phone' => trim($this->phone),
            'notes' => trim($this->notes),
            'status' => 'pending',
        ]);

        $item = $action->execute($dto);

        $this->created = true;
        $this->createdLabel = $item->desired_domain;
        $this->dispatch('website-offer-request-created', id: $item->id);
        $this->dispatch('toast', message: 'Kërkesa juaj për Uebsajt u dërgua me sukses! 🚀', type: 'success');
    }

    public function addAnother()
    {
        $this->reset(['created', 'createdLabel', 'notes']);
        $this->mount();
    }
}
