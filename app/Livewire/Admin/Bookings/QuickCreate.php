<?php

namespace App\Livewire\Admin\Bookings;

use App\Models\Booking;
use App\Domain\Booking\DTOs\BookingDTO;
use App\Domain\Booking\Actions\CreateBookingAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class QuickCreate extends Component
{
        use WithPagination;
     public $barber_shop_id = '';
    public $barber_id = '';
    public $service_id = '';
    public $customer_id = '';
    public $appointment_at = '';
    public $status = '';
    public $total_price = '';
    public $notes = '';
    public $source = '';

    #[On('barber-shop-created')]
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }

    #[On('barber-created')]
    public function refreshBarbers($id) { $this->barber_id = $id; $this->updatedBarberId($id); }

    #[On('service-created')]
    public function refreshServices($id) { $this->service_id = $id; $this->updatedServiceId($id); }

    #[On('customer-created')]
    public function refreshCustomers($id) { $this->customer_id = $id; $this->updatedCustomerId($id); }

    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
        if (isset($related->barber_id)) { $this->barber_id = $related->barber_id; }
        if (isset($related->service_id)) { $this->service_id = $related->service_id; }
        if (isset($related->customer_id)) { $this->customer_id = $related->customer_id; }
    }

    public function updatedBarberId($value)
    {
        if (!$value) return;
        $related = \App\Models\Barber::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id)) { $this->barber_shop_id = $related->barber_shop_id; }
        if (isset($related->service_id)) { $this->service_id = $related->service_id; }
        if (isset($related->customer_id)) { $this->customer_id = $related->customer_id; }
    }

    public function updatedServiceId($value)
    {
        if (!$value) return;
        $related = \App\Models\Service::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id)) { $this->barber_shop_id = $related->barber_shop_id; }
        if (isset($related->barber_id)) { $this->barber_id = $related->barber_id; }
        if (isset($related->customer_id)) { $this->customer_id = $related->customer_id; }

        // Auto-fill price
        if (isset($related->price)) {
            $this->total_price = $related->price;
        }
    }

    public function updatedCustomerId($value)
    {
        if (!$value) return;
        $related = \App\Models\Customer::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id)) { $this->barber_shop_id = $related->barber_shop_id; }
        if (isset($related->barber_id)) { $this->barber_id = $related->barber_id; }
        if (isset($related->service_id)) { $this->service_id = $related->service_id; }
    }

    protected function getbarberShopsList() {
        $query = \App\Models\BarberShop::query();
        if (method_exists(\App\Models\BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    protected function getbarbersList() {
        $query = \App\Models\Barber::query();
        if (method_exists(\App\Models\Barber::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    protected function getservicesList() {
        $query = \App\Models\Service::query();
        if (method_exists(\App\Models\Service::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    protected function getcustomersList() {
        $query = \App\Models\Customer::query();
        if (method_exists(\App\Models\Customer::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.bookings.quick-create', [
            'barberShops' => $this->getbarberShopsList(),
            'barbers' => $this->getbarbersList(),
            'services' => $this->getservicesList(),
            'customers' => $this->getcustomersList(),
        ]); }

    public function store(CreateBookingAction $action)
    {
        $this->validate();
        $dto = BookingDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'service_id' => $this->service_id,
            'customer_id' => $this->customer_id,
            'appointment_at' => $this->appointment_at,
            'status' => $this->status,
            'total_price' => $this->total_price,
            'notes' => $this->notes,
            'source' => $this->source,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('booking-created', id: $item->id);
        $this->js("Livewire.dispatch('booking-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('bookings.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['barber_shop_id', 'barber_id', 'service_id', 'customer_id', 'appointment_at', 'status', 'total_price', 'notes', 'source']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = Booking::rules();
        return $rules; }
}
