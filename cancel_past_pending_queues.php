<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pastQueues = \App\Models\MessageQueue::where('status', 'pending')
    ->where('scheduled_at', '<', now()->subMinutes(15))
    ->get();

echo "Found " . $pastQueues->count() . " past pending SMS queue items.\n";

foreach ($pastQueues as $q) {
    $q->update(['status' => 'failed']);
    echo "Cancelled past queue ID #{$q->id} (scheduled_at {$q->scheduled_at})\n";
}
