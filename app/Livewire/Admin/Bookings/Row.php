<?php

namespace App\Livewire\Admin\Bookings;

use App\Models\Booking;
use Livewire\Component;

class Row extends Component
{
    public Booking $item;

    public function payNow($amount = null, $method = 'cash')
    {
        abort_if_cannot('edit_bookings');

        try {
            $payAmount = (float) ($amount !== null && is_numeric($amount) && (float)$amount >= 0 ? $amount : $this->item->total_price);

            // 1. Create or update Payment record
            \App\Models\Payment::updateOrCreate(
                ['booking_id' => $this->item->id],
                [
                    'barber_shop_id' => $this->item->barber_shop_id,
                    'amount' => $payAmount,
                    'method' => in_array($method, ['cash', 'card'], true) ? $method : 'cash',
                    'status' => 'paid',
                ]
            );

            // 2. Update booking status to completed and price to paid amount
            $this->item->update([
                'status' => 'completed',
                'total_price' => $payAmount,
            ]);

            // 3. Refresh model instance locally
            $this->item->refresh();

            $this->dispatch('toast', message: __('bookings.Payment recorded successfully!'), type: 'success');
            $this->dispatch('refreshData');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: __('bookings.Payment error: ') . $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.bookings.row');
    }
}
