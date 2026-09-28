<?php

namespace App\Domain\Payment\Actions;

use App\Models\Payment;
use App\Domain\Payment\DTOs\PaymentDTO;
use App\Models\AuditTrail;

class CreatePaymentAction
{
    public function execute(PaymentDTO $dto): Payment
    {
        if ($dto->booking_id) {
            $existing = Payment::where('booking_id', $dto->booking_id)->first();
            if ($existing) {
                $existing->update($dto->toArray());
                if ($existing->booking_id) {
                    $booking = \App\Models\Booking::find($existing->booking_id);
                    if ($booking) {
                        $status = ($existing->status === 'paid' || $existing->status === 'completed') ? 'paid' : 'unpaid';
                        $booking->update([
                            'payment_status' => $status,
                            'total_price' => $existing->amount,
                        ]);
                    }
                }
                AuditTrail::log($existing, 'update', 'Payments');
                return $existing->fresh();
            }
        }

        $item = Payment::create($dto->toArray());
        if ($item->booking_id) {
            $booking = \App\Models\Booking::find($item->booking_id);
            if ($booking) {
                $status = ($item->status === 'paid' || $item->status === 'completed') ? 'paid' : 'unpaid';
                $booking->update([
                    'payment_status' => $status,
                    'total_price' => $item->amount,
                ]);
            }
        }
        AuditTrail::log($item, 'create', 'Payments');
        return $item;
    }
}
