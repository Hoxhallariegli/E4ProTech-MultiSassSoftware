<?php

namespace App\Livewire\Admin\Reviews;

use App\Models\Review;
use App\Domain\Review\Queries\ReviewListQuery;
use App\Domain\Review\Actions\DeleteReviewAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Reviews')]
class Reviews extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $barber_shop_id = '';
    #[Url(history: true)] public $barber_id = '';
    #[Url(history: true)] public $customer_id = '';
    #[Url(history: true)] public $booking_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'barber_shop_id', 'barber_id', 'customer_id', 'booking_id', ]); $this->resetPage(); }

    #[On('echo-private:mobile.reviews,.reviews.changed')]
    public function onBroadcastCloudChange($event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_reviews');
        $query = (new ReviewListQuery())->handle(['search' => $this->search,             'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'customer_id' => $this->customer_id,
            'booking_id' => $this->booking_id,
], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.reviews.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => Review::sortable(),
            'barberShops' => \App\Models\BarberShop::pluck('name', 'id')->toArray(),
            'barbers' => \App\Models\Barber::pluck('name', 'id')->toArray(),
            'customers' => \App\Models\Customer::pluck('name', 'id')->toArray(),
            'bookings' => \App\Models\Booking::with('customer')->get()->pluck('customer.name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, Review::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteReview($id, DeleteReviewAction $action) 
    {
        abort_if_cannot('delete_reviews');
        $item = Review::find($id);
        if (!$item) { $this->dispatch('toast', message: __('reviews.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('reviews.deleted'), type: 'success'); $this->resetPage(); } 
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('reviews.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('reviews.delete_error'), type: 'error'); }
    }
}