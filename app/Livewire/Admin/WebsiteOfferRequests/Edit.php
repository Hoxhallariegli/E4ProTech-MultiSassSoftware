<?php

namespace App\Livewire\Admin\WebsiteOfferRequests;

use App\Models\WebsiteOfferRequest;
use App\Models\BarberShop;
use App\Domain\WebsiteOfferRequest\DTOs\WebsiteOfferRequestDTO;
use App\Domain\WebsiteOfferRequest\Actions\UpdateWebsiteOfferRequestAction;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Ndrysho Kërkesën për Uebsajt')]
class Edit extends Component
{
    public WebsiteOfferRequest $item;
    public $barber_shop_id = '';
    public $desired_domain = '';
    public $phone = '';
    public $notes = '';
    public $status = 'pending';

    public function mount(WebsiteOfferRequest $websiteOfferRequest)
    {
        $this->item = $websiteOfferRequest;
        $this->fill($websiteOfferRequest->toArray());
    }

    public function render()
    {
        return view('livewire.admin.website-offer-requests.edit', [
            'shops' => BarberShop::pluck('name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function update(UpdateWebsiteOfferRequestAction $action)
    {
        $this->validate([
            'barber_shop_id' => 'required|exists:barber_shops,id',
            'desired_domain' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|string|in:pending,contacted,completed',
        ]);

        $dto = WebsiteOfferRequestDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'user_id' => $this->item->user_id,
            'desired_domain' => trim($this->desired_domain),
            'phone' => trim($this->phone),
            'notes' => trim($this->notes),
            'status' => $this->status,
        ]);

        $action->execute($this->item, $dto);

        session()->flash('success', 'Kërkesa për uebsajt u përditësua me sukses.');
        return to_route('admin.website-offer-requests.index');
    }
}
