<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Services\GroqService;
use App\Models\BarberShop;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\AuditTrail;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function process(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|min:2',
        ]);

        $user = $request->user();
        $shop = $user->barberShop;

        if (!$shop && $user->is_global_admin) {
            $shop = BarberShop::first();
        }

        if (!$shop) {
            return response()->json(['message' => 'Nuk u gjet asnjë sallon aktiv.'], 422);
        }

        $ai = new GroqService();
        $actions = $ai->interpretOperationalPrompt($request->prompt);

        $executedResults = [];

        foreach ($actions as $action) {
            $type = $action['action'] ?? '';
            $details = $action['details'] ?? [];

            switch ($type) {
                case 'create_services':
                    $executedResults[] = $this->executeCreateServices($shop, $details);
                    break;
                case 'create_bookings':
                    $executedResults[] = $this->executeCreateBookings($shop, $user, $details);
                    break;
                case 'create_staff':
                    $executedResults[] = $this->executeCreateStaff($shop, $user, $details);
                    break;
                case 'create_customers':
                    $executedResults[] = $this->executeCreateCustomers($shop, $details);
                    break;
                case 'query_stats':
                default:
                    $executedResults[] = $this->executeQueryStats($shop);
                    break;
            }
        }

        return response()->json([
            'message' => 'Kërkesa u ekzekutua me sukses te bazat e të dhënave & Audit Trails!',
            'results' => $executedResults,
        ]);
    }

    protected function executeCreateServices(BarberShop $shop, array $details): array
    {
        $servicesList = $details['services'] ?? [];
        $created = [];
        $defaultPrice = in_array(strtoupper($shop->resolved_currency), ['€', '$', 'CHF', 'GBP', 'EUR', 'USD']) ? 15 : 1000;

        foreach ($servicesList as $item) {
            $name = trim($item['name'] ?? 'Shërbim AI');
            $price = (float) ($item['price'] ?? $defaultPrice);
            $duration = (int) ($item['duration_minutes'] ?? 30);

            $service = Service::create([
                'barber_shop_id' => $shop->id,
                'name' => $name,
                'description' => 'Shërbim i krijuar automatikisht nga Mobile AI Assistant',
                'price' => $price,
                'duration_minutes' => $duration,
                'category' => $shop->resolved_service_label,
                'active' => true,
            ]);

            AuditTrail::log($service, 'create', 'Services');
            $created[] = "#{$service->id} - {$service->name} (" . format_currency($service->price, $shop) . ", {$service->duration_minutes}min)";
        }

        return [
            'title' => "Krijimi i " . count($created) . " Shërbimeve të Reja",
            'badge' => 'Shërbime',
            'items' => $created,
        ];
    }

    protected function executeCreateBookings(BarberShop $shop, $user, array $details): array
    {
        $count = max(1, (int) ($details['count'] ?? 5));
        $startHour = (int) ($details['start_hour'] ?? 13);
        $endHour = (int) ($details['end_hour'] ?? 17);

        if ($endHour <= $startHour) $endHour = $startHour + 4;

        $barber = Barber::where('barber_shop_id', $shop->id)->where('active', true)->first();
        if (!$barber) {
            $barber = Barber::create([
                'barber_shop_id' => $shop->id,
                'user_id' => $user->id,
                'name' => 'Staf Kryesor',
                'active' => true,
            ]);
            AuditTrail::log($barber, 'create', 'Barbers');
        }

        $service = Service::where('barber_shop_id', $shop->id)->where('active', true)->first();
        if (!$service) {
            $defaultPrice = in_array(strtoupper($shop->resolved_currency), ['€', '$', 'CHF', 'GBP', 'EUR', 'USD']) ? 15 : 1000;
            $service = Service::create([
                'barber_shop_id' => $shop->id,
                'name' => 'Prerje & Stilim',
                'price' => $defaultPrice,
                'duration_minutes' => 30,
                'active' => true,
            ]);
            AuditTrail::log($service, 'create', 'Services');
        }

        $today = now()->format('Y-m-d');
        $hourStep = max(1, (int) ceil(($endHour - $startHour) / $count));

        $createdBookings = [];
        $sampleNames = ['Alban Hoxha', 'Erion Marku', 'Sokol Prifti', 'Gentian Krasniqi', 'Dritan Basha', 'Andi Topalli', 'Klodian Vaso'];

        for ($i = 0; $i < $count; $i++) {
            $custName = $sampleNames[$i % count($sampleNames)] . " (Walk-In)";
            $customer = Customer::firstOrCreate(
                ['barber_shop_id' => $shop->id, 'phone' => '+35569' . rand(1000000, 9999999)],
                ['name' => $custName]
            );
            AuditTrail::log($customer, 'create', 'Customers');

            $hour = min($endHour - 1, $startHour + ($i * $hourStep));
            $min = [0, 15, 30, 45][rand(0, 3)];
            $appointmentAt = Carbon::parse("{$today} {$hour}:{$min}:00");

            $booking = Booking::create([
                'barber_shop_id' => $shop->id,
                'barber_id' => $barber->id,
                'service_id' => $service->id,
                'customer_id' => $customer->id,
                'appointment_at' => $appointmentAt,
                'status' => 'confirmed',
                'payment_status' => 'unpaid',
                'total_price' => $service->price,
                'notes' => 'Walk-In • Krijuar automatikisht nga Mobile AI Assistant (Pa SMS)',
                'source' => 'walk-in',
            ]);

            AuditTrail::log($booking, 'create', 'Bookings');
            $createdBookings[] = "#{$booking->id} - {$customer->name} ({$barber->name}) në orën {$appointmentAt->format('H:i')} - " . format_currency($booking->total_price, $shop);
        }

        return [
            'title' => "Krijimi i {$count} Rezervimeve Walk-In për Sot (Pa SMS)",
            'badge' => 'Rezervime',
            'items' => $createdBookings,
        ];
    }

    protected function executeCreateStaff(BarberShop $shop, $user, array $details): array
    {
        $staffList = $details['staff'] ?? [];
        $created = [];

        foreach ($staffList as $item) {
            $name = trim($item['name'] ?? 'Staf i Ri');
            $barber = Barber::create([
                'barber_shop_id' => $shop->id,
                'user_id' => $user->id,
                'name' => $name,
                'phone' => '+35569' . rand(1000000, 9999999),
                'active' => true,
            ]);

            AuditTrail::log($barber, 'create', 'Barbers');
            $created[] = "#{$barber->id} - {$barber->name}";
        }

        return [
            'title' => "Shtimi i " . count($created) . " Pjesëtarëve të Stafit",
            'badge' => 'Staf',
            'items' => $created,
        ];
    }

    protected function executeCreateCustomers(BarberShop $shop, array $details): array
    {
        $customersList = $details['customers'] ?? [];
        $created = [];

        foreach ($customersList as $item) {
            $name = trim($item['name'] ?? 'Klient i Ri');
            $customer = Customer::create([
                'barber_shop_id' => $shop->id,
                'name' => $name,
                'phone' => '+35569' . rand(1000000, 9999999),
            ]);

            AuditTrail::log($customer, 'create', 'Customers');
            $created[] = "#{$customer->id} - {$customer->name}";
        }

        return [
            'title' => "Shtimi i " . count($created) . " Klientëve të Rinj",
            'badge' => 'Klientë',
            'items' => $created,
        ];
    }

    protected function executeQueryStats(BarberShop $shop): array
    {
        $todayBookings = Booking::where('barber_shop_id', $shop->id)->whereDate('appointment_at', today())->count();
        $totalServices = Service::where('barber_shop_id', $shop->id)->count();
        $totalStaff = Barber::where('barber_shop_id', $shop->id)->count();
        $totalRevenueToday = Booking::where('barber_shop_id', $shop->id)->whereDate('appointment_at', today())->sum('total_price');

        return [
            'title' => "Statistikat e Sotme për {$shop->name}",
            'badge' => 'Analizë',
            'items' => [
                "Rezervime Sot: {$todayBookings} Takime",
                "Xhiro e Parashikuar Sot: " . format_currency($totalRevenueToday, $shop),
                "Stafi Aktiv: {$totalStaff} " . $shop->resolved_staff_label_plural,
                "Shërbime të Aktivizuara: {$totalServices} Opsione",
            ],
        ];
    }
}
