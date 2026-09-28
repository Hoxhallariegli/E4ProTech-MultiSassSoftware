<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = Illuminate\Http\Request::create('/api/mobile/bookings/day-schedule?date=2026-09-26&barber_id=3', 'GET');
$controller = new App\Http\Controllers\Api\Mobile\BookingController();
$response = $controller->daySchedule($req);
echo $response->getContent();
