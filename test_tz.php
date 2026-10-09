<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = \App\Models\Booking::find(36);
echo "Booking ID: " . ($booking?->id ?? 'NONE') . "\n";
if ($booking) {
    echo "appointment_at (Formatted): " . $booking->appointment_at->format('Y-m-d H:i:s') . "\n";
    echo "created_at (Formatted): " . $booking->created_at->format('Y-m-d H:i:s') . "\n";
}
echo "Carbon Now: " . \Carbon\Carbon::now()->format('Y-m-d H:i:s') . "\n";
echo "Timezone: " . config('app.timezone') . "\n";
