<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_cache = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(App\Models\Payment::all() as $p) {
    echo "Payment ID: {$p->id} | Booking ID: {$p->booking_id} | Amount: {$p->amount}\n";
}
foreach(App\Models\Booking::all() as $b) {
    echo "Booking ID: {$b->id} | Total Price: {$b->total_price} | Service ID: {$b->service_id}\n";
}
