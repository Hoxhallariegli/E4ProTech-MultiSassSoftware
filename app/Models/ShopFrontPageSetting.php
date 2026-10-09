<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopFrontPageSetting extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;
    protected $fillable = ['hero_title', 'hero_subtitle', 'hero_button_text', 'services_badge_text', 'services_title', 'staff_badge_text', 'staff_title', 'contact_phone', 'contact_email', 'contact_address', 'google_maps_url', 'footer_text', 'barber_shop_id'];
    protected function casts(): array { return [
        ]; }
    public static function rules($id = null): array { return [
            'hero_title' => ['nullable', 'string'],
            'hero_subtitle' => ['nullable', 'string'],
            'hero_button_text' => ['nullable', 'string', 'max:255'],
            'services_badge_text' => ['nullable', 'string', 'max:255'],
            'services_title' => ['nullable', 'string', 'max:255'],
            'staff_badge_text' => ['nullable', 'string', 'max:255'],
            'staff_title' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'string', 'max:255'],
            'contact_address' => ['nullable', 'string', 'max:255'],
            'google_maps_url' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            'barber_shop_id' => ['required', 'integer'],
        ]; }
    public static function sortable(): array { return ['id', 'hero_title', 'hero_subtitle', 'hero_button_text', 'services_badge_text', 'services_title', 'staff_badge_text', 'staff_title', 'contact_phone', 'contact_email', 'contact_address', 'google_maps_url', 'footer_text']; }

    protected static function booted(): void
    {
        static::observe(\App\Observers\ShopFrontPageSettingObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id'); }

}