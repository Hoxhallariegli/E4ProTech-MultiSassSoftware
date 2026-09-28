<?php

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use App\Domain\Payment\DTOs\PaymentDTO;
use App\Domain\Payment\Actions\CreatePaymentAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Add Payment')]
class Create extends Component
{
        use WithPagination;
     public $barber_shop_id = '';
    public $booking_id = '';
    public $amount = '';
    public $method = '';
    public $status = '';
 
    #[On('barber-shop-created')] 
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }

    #[On('booking-created')] 
    public function refreshBookings($id) { $this->booking_id = $id; $this->updatedBookingId($id); }
 
    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
        if (isset($related->booking_id)) { $this->booking_id = $related->booking_id; }
    }

    public function updatedBookingId($value)
    {
        if (!$value) return;
        $related = \App\Models\Booking::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id)) { $this->barber_shop_id = $related->barber_shop_id; }
    }
 
    protected function getbarberShopsList() {
        $query = \App\Models\BarberShop::query();
        if (method_exists(\App\Models\BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    protected function getbookingsList() {
        $query = \App\Models\Booking::query();
        if (method_exists(\App\Models\Booking::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->with('customer')->get()->pluck('customer.name', 'id')->toArray();
    }
 
    public function mount() { if (auth()->check() && auth()->user()->barber_shop_id) { $this->barber_shop_id = auth()->user()->barber_shop_id; } }

    public function render() { abort_if_cannot('add_payments'); return view('livewire.admin.payments.create', [
            'barberShops' => $this->getbarberShopsList(),
            'bookings' => $this->getbookingsList(),
        ])->layout('components.layouts.app'); }
    public function store(CreatePaymentAction $action) { $this->validate();  $dto = PaymentDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'booking_id' => $this->booking_id,
            'amount' => $this->amount,
            'method' => $this->method,
            'status' => $this->status,
        ]); $action->execute($dto); session()->flash('success', __('payments.created')); return to_route('admin.payments.index'); }
    protected function rules(): array { $rules = Payment::rules();
        return $rules; }
}