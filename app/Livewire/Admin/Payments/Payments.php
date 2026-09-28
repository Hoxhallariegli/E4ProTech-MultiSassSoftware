<?php

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use App\Domain\Payment\Queries\PaymentListQuery;
use App\Domain\Payment\Actions\DeletePaymentAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Payments')]
class Payments extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $barber_shop_id = '';
    #[Url(history: true)] public $booking_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'barber_shop_id', 'booking_id', ]); $this->resetPage(); }

    #[On('echo-private:mobile.payments,.payments.changed')]
    public function onBroadcastCloudChange($event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_payments');
        $query = (new PaymentListQuery())->handle(['search' => $this->search,             'barber_shop_id' => $this->barber_shop_id,
            'booking_id' => $this->booking_id,
], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.payments.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => Payment::sortable(),
            'barberShops' => \App\Models\BarberShop::pluck('name', 'id')->toArray(),
            'bookings' => \App\Models\Booking::with('customer')->get()->pluck('customer.name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, Payment::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deletePayment($id, DeletePaymentAction $action) 
    {
        abort_if_cannot('delete_payments');
        $item = Payment::find($id);
        if (!$item) { $this->dispatch('toast', message: __('payments.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('payments.deleted'), type: 'success'); $this->resetPage(); } 
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('payments.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('payments.delete_error'), type: 'error'); }
    }
}