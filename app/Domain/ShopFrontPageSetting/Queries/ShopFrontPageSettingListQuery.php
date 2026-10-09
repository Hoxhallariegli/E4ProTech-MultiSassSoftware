<?php

namespace App\Domain\ShopFrontPageSetting\Queries;

use App\Models\ShopFrontPageSetting;
use Illuminate\Database\Eloquent\Builder;

class ShopFrontPageSettingListQuery
{
    public function handle(array $params = [], string $sortField = 'id', string $sortAsc = 'asc'): Builder
    {
        $query = ShopFrontPageSetting::query()->with(['barberShop']);
        if (isset($params['search']) && $params['search']) {
            $query->where(function($query) use ($params) {
                $query->where('id', 'like', '%' . $params['search'] . '%');
                $query->orWhere('hero_title', 'like', '%' . $params['search'] . '%');
                $query->orWhere('hero_subtitle', 'like', '%' . $params['search'] . '%');
                $query->orWhere('hero_button_text', 'like', '%' . $params['search'] . '%');
                $query->orWhere('services_badge_text', 'like', '%' . $params['search'] . '%');
                $query->orWhere('services_title', 'like', '%' . $params['search'] . '%');
                $query->orWhere('staff_badge_text', 'like', '%' . $params['search'] . '%');
                $query->orWhere('staff_title', 'like', '%' . $params['search'] . '%');
                $query->orWhere('contact_phone', 'like', '%' . $params['search'] . '%');
                $query->orWhere('contact_email', 'like', '%' . $params['search'] . '%');
                $query->orWhere('contact_address', 'like', '%' . $params['search'] . '%');
                $query->orWhere('google_maps_url', 'like', '%' . $params['search'] . '%');
                $query->orWhere('footer_text', 'like', '%' . $params['search'] . '%');
            });
        }
        if (isset($params['barber_shop_id']) && $params['barber_shop_id']) $query->where('barber_shop_id', $params['barber_shop_id']);
        $sortField = in_array($sortField, ShopFrontPageSetting::sortable(), true) ? $sortField : 'id';
        $sortAsc = in_array(strtolower((string) $sortAsc), ['asc', 'desc'], true) ? $sortAsc : 'asc';
        return $query->orderBy($sortField, $sortAsc);
    }
}