<?php

namespace App\Livewire\Admin\Bookings;

use App\Models\Booking;
use App\Domain\Booking\Queries\BookingListQuery;
use App\Domain\Booking\Actions\DeleteBookingAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Bookings')]
class Bookings extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $barber_shop_id = '';
    #[Url(history: true)] public $barber_id = '';
    #[Url(history: true)] public $service_id = '';
    #[Url(history: true)] public $customer_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'barber_shop_id', 'barber_id', 'service_id', 'customer_id', ]); $this->resetPage(); }

    #[On('echo-private:mobile.bookings,.bookings.changed')]
    public function onBroadcastCloudChange($event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_bookings');
        $query = (new BookingListQuery())->handle(['search' => $this->search,             'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'service_id' => $this->service_id,
            'customer_id' => $this->customer_id,
], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.bookings.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => Booking::sortable(),
            'barberShops' => \App\Models\BarberShop::pluck('name', 'id')->toArray(),
            'barbers' => \App\Models\Barber::pluck('name', 'id')->toArray(),
            'services' => \App\Models\Service::pluck('name', 'id')->toArray(),
            'customers' => \App\Models\Customer::pluck('name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, Booking::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteBooking($id, DeleteBookingAction $action) 
    {
        abort_if_cannot('delete_bookings');
        $item = Booking::find($id);
        if (!$item) { $this->dispatch('toast', message: __('bookings.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('bookings.deleted'), type: 'success'); $this->resetPage(); } 
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('bookings.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('bookings.delete_error'), type: 'error'); }
    }
}