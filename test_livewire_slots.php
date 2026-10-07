<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$shop = \App\Models\BarberShop::where('slug', 'elegance-beauty-salon')->first();
$comp = new \App\Livewire\PublicShopBooking();
$comp->mount($shop);

echo "Shop: {$shop->name} (ID: {$shop->id})\n";
echo "Selected Barber: {$comp->selectedBarberId}\n";
echo "Selected Service: {$comp->selectedServiceId}\n";
echo "Initial bookingDate: {$comp->bookingDate}\n";

$slots1 = $comp->getAvailableTimeSlotsProperty();
echo "Slots count for initial date ({$comp->bookingDate}): " . count($slots1) . "\n";
print_r($slots1);

$comp->bookingDate = '2026-10-08';
$slots2 = $comp->getAvailableTimeSlotsProperty();
echo "Slots count for 2026-10-08: " . count($slots2) . "\n";
print_r($slots2);

$comp->bookingDate = '10/08/2026';
$slots3 = $comp->getAvailableTimeSlotsProperty();
echo "Slots count for 10/08/2026: " . count($slots3) . "\n";
print_r($slots3);
