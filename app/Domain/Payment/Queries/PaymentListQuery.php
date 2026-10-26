<?php

namespace App\Domain\Payment\Queries;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

class PaymentListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = Payment::query()->with(['barberShop', 'booking.customer', 'booking.service', 'booking.barber']);

        if (isset($params['search']) && $params['search']) {
            $search = $params['search'];
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                  ->orWhere('amount', 'like', '%' . $search . '%')
                  ->orWhere('method', 'like', '%' . $search . '%')
                  ->orWhere('status', 'like', '%' . $search . '%')
                  ->orWhereHas('booking.customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', '%' . $search . '%')
                         ->orWhere('phone', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('booking.service', function ($sq) use ($search) {
                      $sq->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('booking.barber', function ($bq) use ($search) {
                      $bq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if (isset($params['barber_shop_id']) && $params['barber_shop_id']) {
            $query->where('barber_shop_id', $params['barber_shop_id']);
        }

        if (isset($params['booking_id']) && $params['booking_id']) {
            $query->where('booking_id', $params['booking_id']);
        }

        if (isset($params['barber_id']) && $params['barber_id']) {
            $query->whereHas('booking', function ($bq) use ($params) {
                $bq->where('barber_id', $params['barber_id']);
            });
        }

        if (isset($params['payment_date']) && $params['payment_date']) {
            $query->whereDate('created_at', $params['payment_date']);
        }

        $sortField = in_array($sortField, Payment::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}
