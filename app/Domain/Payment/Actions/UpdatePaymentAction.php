<?php

namespace App\Domain\Payment\Actions;

use App\Models\Payment;
use App\Domain\Payment\DTOs\PaymentDTO;
use App\Models\AuditTrail;

class UpdatePaymentAction
{
    public function execute(Payment $model, PaymentDTO $dto): Payment
    {
        $data = $dto->toArray();
        $model->fill($data);
        $dirty = $model->getDirty();
        $original = $model->getOriginal();
        $model->save();

        if (!empty($dirty)) {
            AuditTrail::create([
                'user_id' => auth()->id(),
                'title' => "Update Payment",
                'reference_id' => (string)$model->id,
                'section' => 'Payments',
                'type' => 'update',
                'old_values' => array_intersect_key($original, $dirty),
                'new_values' => $dirty,
            ]);
        }

        if ($model->booking_id) {
            $booking = \App\Models\Booking::find($model->booking_id);
            if ($booking) {
                $status = ($model->status === 'paid' || $model->status === 'completed') ? 'paid' : 'unpaid';
                $booking->update([
                    'payment_status' => $status,
                    'total_price' => $model->amount,
                ]);
            }
        }
        return $model->fresh();
    }
}
