<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$trialPlan = \App\Models\Plan::where('name', 'Trial')->orWhere('price', 0)->first() ?? \App\Models\Plan::first();
$shops = \App\Models\BarberShop::doesntHave('subscriptions')->get();

echo "Found " . $shops->count() . " shops without subscriptions.\n";

foreach ($shops as $shop) {
    \App\Models\Subscription::create([
        'barber_shop_id' => $shop->id,
        'plan_id' => $trialPlan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addDays(30),
        'auto_renew' => true,
    ]);
    echo "Created Trial Subscription for Shop #{$shop->id} ({$shop->name})\n";
}
