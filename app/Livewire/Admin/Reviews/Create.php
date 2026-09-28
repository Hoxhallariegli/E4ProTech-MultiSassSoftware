<?php

namespace App\Livewire\Admin\Reviews;

use App\Models\Review;
use App\Domain\Review\DTOs\ReviewDTO;
use App\Domain\Review\Actions\CreateReviewAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Add Review')]
class Create extends Component
{
        use WithPagination;
     public $barber_shop_id = '';
    public $barber_id = '';
    public $customer_id = '';
    public $booking_id = '';
    public $rating = '';
    public $comment = '';
 
    #[On('barber-shop-created')] 
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }

    #[On('barber-created')] 
    public function refreshBarbers($id) { $this->barber_id = $id; $this->updatedBarberId($id); }

    #[On('customer-created')] 
    public function refreshCustomers($id) { $this->customer_id = $id; $this->updatedCustomerId($id); }

    #[On('booking-created')] 
    public function refreshBookings($id) { $this->booking_id = $id; $this->updatedBookingId($id); }
 
    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
        if (isset($related->barber_id)) { $this->barber_id = $related->barber_id; }
        if (isset($related->customer_id)) { $this->customer_id = $related->customer_id; }
        if (isset($related->booking_id)) { $this->booking_id = $related->booking_id; }
    }

    public function updatedBarberId($value)
    {
        if (!$value) return;
        $related = \App\Models\Barber::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id)) { $this->barber_shop_id = $related->barber_shop_id; }
        if (isset($related->customer_id)) { $this->customer_id = $related->customer_id; }
        if (isset($related->booking_id)) { $this->booking_id = $related->booking_id; }
    }

    public function updatedCustomerId($value)
    {
        if (!$value) return;
        $related = \App\Models\Customer::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id)) { $this->barber_shop_id = $related->barber_shop_id; }
        if (isset($related->barber_id)) { $this->barber_id = $related->barber_id; }
        if (isset($related->booking_id)) { $this->booking_id = $related->booking_id; }
    }

    public function updatedBookingId($value)
    {
        if (!$value) return;
        $related = \App\Models\Booking::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id)) { $this->barber_shop_id = $related->barber_shop_id; }
        if (isset($related->barber_id)) { $this->barber_id = $related->barber_id; }
        if (isset($related->customer_id)) { $this->customer_id = $related->customer_id; }
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

    protected function getcustomersList() {
        $query = \App\Models\Customer::query();
        if (method_exists(\App\Models\Customer::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    protected function getbookingsList() {
        $query = \App\Models\Booking::query();
        if (method_exists(\App\Models\Booking::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->with('customer')->get()->pluck('customer.name', 'id')->toArray();
    }
 
    public function mount() { if (auth()->check() && auth()->user()->barber_shop_id) { $this->barber_shop_id = auth()->user()->barber_shop_id; } }

    public function render() { abort_if_cannot('add_reviews'); return view('livewire.admin.reviews.create', [
            'barberShops' => $this->getbarberShopsList(),
            'barbers' => $this->getbarbersList(),
            'customers' => $this->getcustomersList(),
            'bookings' => $this->getbookingsList(),
        ])->layout('components.layouts.app'); }
    public function store(CreateReviewAction $action) { $this->validate();  $dto = ReviewDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'customer_id' => $this->customer_id,
            'booking_id' => $this->booking_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]); $action->execute($dto); session()->flash('success', __('reviews.created')); return to_route('admin.reviews.index'); }
    protected function rules(): array { $rules = Review::rules();
        return $rules; }
}