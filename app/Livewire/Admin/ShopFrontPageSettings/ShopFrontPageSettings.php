<?php

namespace App\Livewire\Admin\ShopFrontPageSettings;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use App\Models\BarberShop;
use App\Models\ShopFrontPageSetting;
use App\Services\ImageUploadService;
use App\Services\ShopTranslationService;

#[Title('Faqja Ime - Front Page Manager')]
class ShopFrontPageSettings extends Component
{
    use WithFileUploads;

    public BarberShop $shop;

    // Shop Branding
    public $name = '';
    public $app_name = '';
    public $logo = null;
    public $banner = null;
    public $primary_color = '#FF9F0A';
    public $secondary_color = '#1C1C1E';
    public $business_type = 'barber';
    public $phone = '';
    public $email = '';

    // Front Page Text Settings
    public $hero_badge_text = '';
    public $hero_title = '';
    public $hero_subtitle = '';
    public $hero_button_text = '';
    public $services_badge_text = '';
    public $services_title = '';
    public $staff_badge_text = '';
    public $staff_title = '';
    public $contact_address = '';
    public $google_maps_url = '';
    public $footer_text = '';

    // Translations
    public $activeLocale = 'sq';
    public $translations = [
        'sq' => [],
        'en' => [],
        'it' => [],
        'de' => [],
        'fr' => [],
    ];

    public function mount()
    {
        $user = auth()->user();
        $shop = $user?->barberShop;

        if (!$shop && $user?->is_global_admin) {
            $shop = BarberShop::first();
        }

        if (!$shop) {
            abort(404, 'Nuk u gjet asnjë sallon.');
        }

        $this->shop = $shop;
        $this->name = $shop->name;
        $this->app_name = $shop->app_name ?: $shop->name;
        $this->primary_color = $shop->primary_color ?: '#FF9F0A';
        $this->secondary_color = $shop->secondary_color ?: '#1C1C1E';
        $this->business_type = $shop->business_type ?: 'barber';
        $this->phone = $shop->phone ?? '';
        $this->email = $shop->email ?? '';

        $frontSetting = ShopFrontPageSetting::firstOrCreate([
            'barber_shop_id' => $shop->id,
        ], [
            'hero_badge_text' => '✨ ' . $shop->resolved_shop_label . ' Zyrtare',
            'hero_title' => 'Eksperiencë Premium për ' . $shop->resolved_service_label . ' & Stilim',
            'hero_subtitle' => 'Rezervoni takimin tuaj online me ekipin tonë profesional në pak sekonda. Zgjidhni shërbimin, orarin dhe stafin tuaj të preferuar 24/7.',
            'hero_button_text' => 'Rezervo Takim Online ↗',
            'services_badge_text' => 'Çmimet & Kohëzgjatja',
            'services_title' => 'Shërbimet e Ofruara',
            'staff_badge_text' => 'Ekipi Ynë',
            'staff_title' => $shop->resolved_staff_label_plural . ' Tanë',
            'contact_phone' => $shop->phone ?? '',
            'contact_email' => $shop->email ?? '',
            'contact_address' => 'Tiranë, Shqipëri',
            'footer_text' => '© ' . date('Y') . ' ' . $shop->name . ' — Mundësuar nga E4ProTech Engine',
        ]);

        $this->hero_badge_text = $frontSetting->hero_badge_text;
        $this->hero_title = $frontSetting->hero_title;
        $this->hero_subtitle = $frontSetting->hero_subtitle;
        $this->hero_button_text = $frontSetting->hero_button_text;
        $this->services_badge_text = $frontSetting->services_badge_text;
        $this->services_title = $frontSetting->services_title;
        $this->staff_badge_text = $frontSetting->staff_badge_text;
        $this->staff_title = $frontSetting->staff_title;
        $this->contact_address = $frontSetting->contact_address;
        $this->google_maps_url = $frontSetting->google_maps_url;
        $this->footer_text = $frontSetting->footer_text;

        $this->translations = array_merge([
            'sq' => [
                'hero_title' => $this->hero_title,
                'hero_subtitle' => $this->hero_subtitle,
                'hero_button_text' => $this->hero_button_text,
            ],
            'en' => [
                'hero_title' => 'Premium Experience for Services & Styling',
                'hero_subtitle' => 'Book your appointment online with our professional team in seconds.',
                'hero_button_text' => 'Book Online Now ↗',
            ],
            'it' => [],
            'de' => [],
            'fr' => [],
        ], $frontSetting->translations ?? []);
    }

    public function setLocale($locale)
    {
        $this->activeLocale = $locale;
    }

    public function saveSettings()
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'primary_color' => 'required|string|max:30',
            'secondary_color' => 'required|string|max:30',
        ]);

        // Upload Logo / Banner
        $logoPath = $this->shop->logo;
        if ($this->logo && !is_string($this->logo)) {
            $logoPath = app(ImageUploadService::class)->upload($this->logo, 'uploads/barber-shops');
        }

        $bannerPath = $this->shop->banner;
        if ($this->banner && !is_string($this->banner)) {
            $bannerPath = app(ImageUploadService::class)->upload($this->banner, 'uploads/barber-shops');
        }

        // Update BarberShop branding
        $this->shop->update([
            'name' => trim($this->name),
            'app_name' => trim($this->app_name),
            'logo' => $logoPath,
            'banner' => $bannerPath,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'business_type' => $this->business_type,
            'phone' => trim($this->phone),
            'email' => trim($this->email),
        ]);

        // Update FrontPageSetting
        ShopFrontPageSetting::updateOrCreate(
            ['barber_shop_id' => $this->shop->id],
            [
                'hero_badge_text' => trim($this->hero_badge_text),
                'hero_title' => trim($this->hero_title),
                'hero_subtitle' => trim($this->hero_subtitle),
                'hero_button_text' => trim($this->hero_button_text),
                'services_badge_text' => trim($this->services_badge_text),
                'services_title' => trim($this->services_title),
                'staff_badge_text' => trim($this->staff_badge_text),
                'staff_title' => trim($this->staff_title),
                'contact_phone' => trim($this->phone),
                'contact_email' => trim($this->email),
                'contact_address' => trim($this->contact_address),
                'google_maps_url' => trim($this->google_maps_url),
                'footer_text' => trim($this->footer_text),
                'translations' => $this->translations,
            ]
        );

        // Update translation file for this shop
        ShopTranslationService::createShopTranslationFiles($this->shop);

        $this->dispatch('toast', message: 'Konfigurimi i Faqes Publike u ruajt me sukses! 🎉', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.shop-front-page-settings.index')->layout('components.layouts.app');
    }
}
