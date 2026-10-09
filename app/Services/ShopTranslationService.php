<?php

namespace App\Services;

use App\Models\BarberShop;
use Illuminate\Support\Facades\File;

class ShopTranslationService
{
    public static function createShopTranslationFiles(BarberShop $shop): void
    {
        $slug = $shop->slug;
        $locales = ['sq', 'en'];

        $sqTexts = [
            'hero_badge' => '✨ ' . $shop->resolved_shop_label . ' Zyrtare',
            'hero_title' => 'Eksperiencë Premium për ' . $shop->resolved_service_label . ' & Stilim',
            'hero_subtitle' => 'Rezervoni takimin tuaj online me ekipin tonë profesional në pak sekonda. Zgjidhni shërbimin, orarin dhe stafin tuaj të preferuar 24/7.',
            'hero_button' => 'Rezervo Takim Online ↗',
            'services_badge' => 'Çmimet & Kohëzgjatja',
            'services_title' => 'Shërbimet e Ofruara',
            'staff_badge' => 'Ekipi Ynë',
            'staff_title' => $shop->resolved_staff_label_plural . ' Tanë',
            'contact_phone' => $shop->phone ?? '',
            'contact_email' => $shop->email ?? '',
            'contact_address' => 'Tiranë, Shqipëri',
            'footer_text' => '© ' . date('Y') . ' ' . $shop->name . ' — Mundësuar nga E4ProTech Engine',
        ];

        $enTexts = [
            'hero_badge' => '✨ Official ' . $shop->resolved_shop_label,
            'hero_title' => 'Premium Experience for Services & Styling',
            'hero_subtitle' => 'Book your appointment online with our professional team in seconds. Choose your preferred service, schedule, and staff 24/7.',
            'hero_button' => 'Book Online Now ↗',
            'services_badge' => 'Pricing & Duration',
            'services_title' => 'Our Services',
            'staff_badge' => 'Our Team',
            'staff_title' => 'Our ' . $shop->resolved_staff_label_plural,
            'contact_phone' => $shop->phone ?? '',
            'contact_email' => $shop->email ?? '',
            'contact_address' => 'Tirana, Albania',
            'footer_text' => '© ' . date('Y') . ' ' . $shop->name . ' — Powered by E4ProTech Engine',
        ];

        foreach ($locales as $lang) {
            $dir = lang_path($lang);
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true, true);
            }

            $path = "$dir/shop-{$slug}.php";
            $data = ($lang === 'sq') ? $sqTexts : $enTexts;

            $content = "<?php\n\nreturn " . var_export($data, true) . ";\n";
            $content = str_replace(['array (', ')'], ['[', ']'], $content);
            File::put($path, $content);
        }
    }

    public static function getShopText(BarberShop $shop, string $key, string $default = ''): string
    {
        $fileKey = "shop-{$shop->slug}.{$key}";
        if (\Illuminate\Support\Facades\Lang::has($fileKey)) {
            return __($fileKey);
        }

        if ($shop->frontPageSetting) {
            $settingVal = $shop->frontPageSetting->getTranslated($key);
            if (!empty($settingVal)) {
                return $settingVal;
            }
        }

        return $default;
    }
}
