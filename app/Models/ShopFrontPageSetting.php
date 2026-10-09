<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopFrontPageSetting extends Model
{
    use HasFactory, \App\Models\Traits\BelongsToBarberShop;

    protected $fillable = [
        'hero_badge_text',
        'hero_title',
        'hero_subtitle',
        'hero_button_text',
        'services_badge_text',
        'services_title',
        'staff_badge_text',
        'staff_title',
        'contact_phone',
        'contact_email',
        'contact_address',
        'google_maps_url',
        'footer_text',
        'translations',
        'barber_shop_id',
    ];

    protected function casts(): array {
        return [
            'translations' => 'array',
        ];
    }

    public function getTranslated(string $key, ?string $locale = null): string
    {
        $lang = $locale ?: app()->getLocale();
        $translations = $this->translations ?? [];

        if (isset($translations[$lang][$key]) && !empty($translations[$lang][$key])) {
            return (string) $translations[$lang][$key];
        }

        return (string) ($this->{$key} ?? '');
    }

    public static function rules($id = null): array {
        return [
            'hero_badge_text' => ['nullable', 'string', 'max:255'],
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
            'translations' => ['nullable', 'array'],
            'barber_shop_id' => ['required', 'integer'],
        ];
    }

    public static function sortable(): array {
        return ['id', 'hero_badge_text', 'hero_title', 'hero_subtitle', 'hero_button_text', 'services_badge_text', 'services_title', 'staff_badge_text', 'staff_title', 'contact_phone', 'contact_email', 'contact_address', 'google_maps_url', 'footer_text'];
    }

    protected static function booted(): void
    {
        static::observe(\App\Observers\ShopFrontPageSettingObserver::class);
    }

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(\App\Models\BarberShop::class, 'barber_shop_id');
    }
}
