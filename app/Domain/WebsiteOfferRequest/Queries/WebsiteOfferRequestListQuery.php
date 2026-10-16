<?php

namespace App\Domain\WebsiteOfferRequest\Queries;

use App\Models\WebsiteOfferRequest;
use Illuminate\Database\Eloquent\Builder;

class WebsiteOfferRequestListQuery
{
    public function handle(array $filters = [], string $sortField = 'id', string $sortOrder = 'desc'): Builder
    {
        $query = WebsiteOfferRequest::with(['barberShop', 'user']);

        if (!auth()->user()?->is_global_admin && auth()->user()?->barber_shop_id) {
            $query->where('barber_shop_id', auth()->user()->barber_shop_id);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('desired_domain', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('barberShop', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy($sortField, $sortOrder);
    }
}
