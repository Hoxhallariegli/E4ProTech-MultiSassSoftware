<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$shop = \App\Models\BarberShop::find(18);
$barber = \App\Models\Barber::where('barber_shop_id', 18)->first();
$service90 = \App\Models\Service::find(11);
$service45 = \App\Models\Service::find(10);

echo "Shop: {$shop->name}, Barber: {$barber->name}\n";
echo "Service 90m: " . ($service90?->name ?? 'NONE') . " (" . ($service90?->duration_minutes ?? 0) . "m)\n";

$comp = new \App\Livewire\PublicShopBooking();
$comp->shop = $shop;
$comp->selectedBarberId = $barber->id;
$comp->selectedServiceId = 11;
$comp->bookingDate = '2026-10-08';

$slots90 = $comp->availableTimeSlots;
echo "Slots for 90m service on 2026-10-08: " . count($slots90) . "\n";
print_r($slots90);

$comp->selectedServiceId = 10;
$slots45 = $comp->availableTimeSlots;
echo "Slots for 45m service on 2026-10-08: " . count($slots45) . "\n";
print_r($slots45);
