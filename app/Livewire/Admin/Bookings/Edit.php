<?php

namespace App\Livewire\Admin\Bookings;

use App\Models\Booking;
use App\Domain\Booking\DTOs\BookingDTO;
use App\Domain\Booking\Actions\UpdateBookingAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit Booking')]
class Edit extends Component
{
        use WithPagination;
 public Booking $item;
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

    public function mount(Booking $booking) { $this->item = $booking; $this->fill($booking->toArray()); $this->appointment_at = $booking->appointment_at?->format('Y-m-d\TH:i'); }
    public function render() { abort_if_cannot('edit_bookings'); return view('livewire.admin.bookings.edit', [
            'barberShops' => $this->getbarberShopsList(),
            'barbers' => $this->getbarbersList(),
            'services' => $this->getservicesList(),
            'customers' => $this->getcustomersList(),
        ])->layout('components.layouts.app'); }
    public function update(UpdateBookingAction $action) { $this->validate();  $dto = BookingDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'service_id' => $this->service_id,
            'customer_id' => $this->customer_id,
            'appointment_at' => $this->appointment_at,
            'status' => $this->status,
            'total_price' => $this->total_price,
            'notes' => $this->notes,
            'source' => $this->source,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('bookings.updated')); return to_route('admin.bookings.index'); }
    protected function rules(): array { $rules = Booking::rules($this->item->id);
        return $rules; }
}
