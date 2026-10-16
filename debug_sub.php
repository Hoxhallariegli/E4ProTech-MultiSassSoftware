<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BarberShop;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;

$shops = BarberShop::all();
echo "--- ALL SHOPS IN MYSQL DATABASE 'grand' ---\n";
foreach ($shops as $shop) {
    echo "ID={$shop->id}, Name={$shop->name}, Owner={$shop->owner_id}, TrialEnds={$shop->trial_ends_at}, ExpiresAt={$shop->expires_at}\n";
}

$shop = BarberShop::find(21) ?? BarberShop::where('name', 'like', '%Station%')->first();
if (!$shop) {
    echo "Shop 21 not found in MySQL\n";
    exit;
}

echo "\n--- INSPECTING SHOP ID {$shop->id}: {$shop->name} ---\n";
$service = app(SubscriptionService::class);
$activeSub = $service->getActiveSubscription($shop);

if ($activeSub) {
    echo "Active Subscription Row Found in `subscriptions` table:\n";
    echo "- ID: {$activeSub->id}\n";
    echo "- Status: {$activeSub->status}\n";
    echo "- Plan ID: {$activeSub->plan_id}\n";
    echo "- Plan Name: " . ($activeSub->plan?->name ?? 'NULL') . "\n";
    echo "- Plan Max Barbers: " . ($activeSub->plan?->max_barbers ?? 'NULL') . "\n";
    echo "- Plan Max Services: " . ($activeSub->plan?->max_services ?? 'NULL') . "\n";
} else {
    echo "Active Subscription Row: NONE FOUND IN `subscriptions` TABLE FOR SHOP ID {$shop->id}!\n";
}

$barbersCount = \App\Models\Barber::where('barber_shop_id', $shop->id)->count();
$servicesCount = \App\Models\Service::where('barber_shop_id', $shop->id)->count();

echo "Current Registered Barbers Count: $barbersCount\n";
echo "Current Registered Services Count: $servicesCount\n";

echo "canAddBarber: " . ($service->canAddBarber($shop) ? "TRUE" : "FALSE") . "\n";
echo "canAddService: " . ($service->canAddService($shop) ? "TRUE" : "FALSE") . "\n";
