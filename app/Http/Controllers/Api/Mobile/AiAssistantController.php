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
use App\Models\AiConversation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function process(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|min:2',
            'confirm' => 'nullable|boolean',
            'actions' => 'nullable|array',
        ]);

        $user = $request->user();
        $shop = $user->barberShop;

        if (!$shop && $user->is_global_admin) {
            $shop = BarberShop::first();
        }

        if (!$shop) {
            return response()->json(['message' => 'Nuk u gjet asnjë sallon aktiv.'], 422);
        }

        $confirm = (bool) $request->input('confirm', false);

        if ($confirm && $request->has('actions')) {
            $actions = $request->input('actions', []);
            $executedResults = $this->executeActions($shop, $user, $actions);

            AiConversation::create([
                'barber_shop_id' => $shop->id,
                'user_id' => $user->id,
                'user_prompt' => $request->prompt,
                'status' => 'success',
                'results' => $executedResults,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Të dhënat u regjistruan me sukses te bazat e të dhënave & Audit Trails! 🚀',
                'results' => $executedResults,
            ]);
        }

        $ai = new GroqService();
        $actions = $ai->interpretOperationalPrompt($request->prompt);

        $requiresConfirmation = false;
        $previews = [];

        foreach ($actions as $action) {
            $type = $action['action'] ?? '';
            $details = $action['details'] ?? [];

            if ($type === 'create_services') {
                $requiresConfirmation = true;
                $services = $details['services'] ?? [];
                $previews[] = [
                    'title' => 'Shtimi i ' . count($services) . ' Shërbimeve të Reja',
                    'icon' => '✂️',
                    'badge' => 'Shërbime',
                    'items' => array_map(fn($s) => ($s['name'] ?? 'Shërbim') . ' (' . format_currency($s['price'] ?? 1000, $shop) . ', ' . ($s['duration_minutes'] ?? 30) . 'min)', $services)
                ];
            } elseif ($type === 'create_bookings') {
                $requiresConfirmation = true;
                $count = $details['count'] ?? 5;
                $previews[] = [
                    'title' => "Krijimi i {$count} Rezervimeve Walk-In për Sot (Pa SMS)",
                    'icon' => '📅',
                    'badge' => 'Rezervime',
                    'items' => ["{$count} Takime sot midis orës " . ($details['start_hour'] ?? 13) . ":00 dhe " . ($details['end_hour'] ?? 17) . ":00 me klientë rastësorë"]
                ];
            } elseif ($type === 'create_staff') {
                $requiresConfirmation = true;
                $staff = $details['staff'] ?? [];
                $previews[] = [
                    'title' => 'Shtimi i ' . count($staff) . ' Pjesëtarëve të Stafit',
                    'icon' => '👥',
                    'badge' => 'Staf',
                    'items' => array_map(fn($st) => $st['name'] ?? 'Staf', $staff)
                ];
            } elseif ($type === 'create_customers') {
                $requiresConfirmation = true;
                $customers = $details['customers'] ?? [];
                $previews[] = [
                    'title' => 'Shtimi i ' . count($customers) . ' Klientëve të Rinj',
                    'icon' => '👤',
                    'badge' => 'Klientë',
                    'items' => array_map(fn($c) => $c['name'] ?? 'Klient', $customers)
                ];
            } elseif ($type === 'delete_bookings') {
                $requiresConfirmation = true;
                $count = $details['count'] ?? 5;
                $ids = $details['ids'] ?? [];
                if (!empty($ids)) {
                    $itemsList = array_map(fn($id) => "Fshirja e Rezervimit #{$id}", $ids);
                } else {
                    $itemsList = ["Të fshihen {$count} rezervimet më të fundit te '{$shop->name}'"];
                }
                $previews[] = [
                    'title' => "Fshirja e {$count} Rezervimeve të Fundit",
                    'icon' => '🗑️',
                    'badge' => 'Fshirje',
                    'items' => $itemsList
                ];
            } elseif ($type === 'query_stats') {
                $res = $this->executeQueryStats($shop);
                AiConversation::create([
                    'barber_shop_id' => $shop->id,
                    'user_id' => $user->id,
                    'user_prompt' => $request->prompt,
                    'status' => 'success',
                    'results' => [$res],
                ]);
                return response()->json([
                    'success' => true,
                    'require_confirmation' => false,
                    'results' => [$res],
                ]);
            }
        }

        if ($requiresConfirmation) {
            return response()->json([
                'success' => true,
                'require_confirmation' => true,
                'prompt' => $request->prompt,
                'greeting' => "Përshëndetje {$user->name}! A dëshironi të procedojmë me kryerjen e këtyre veprimeve?",
                'actions' => $actions,
                'previews' => $previews,
            ]);
        }

        $executedResults = $this->executeActions($shop, $user, $actions);

        AiConversation::create([
            'barber_shop_id' => $shop->id,
            'user_id' => $user->id,
            'user_prompt' => $request->prompt,
            'status' => 'success',
            'results' => $executedResults,
        ]);

        return response()->json([
            'success' => true,
            'require_confirmation' => false,
            'message' => 'Kërkesa u ekzekutua me sukses te bazat e të dhënave & Audit Trails!',
            'results' => $executedResults,
        ]);
    }

    protected function executeActions($shop, $user, array $actions)
    {
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
                case 'delete_bookings':
                    $executedResults[] = $this->executeDeleteBookings($shop, $details);
                    break;
                case 'delete_services':
                    $executedResults[] = $this->executeDeleteServices($shop, $details);
                    break;
                case 'delete_staff':
                    $executedResults[] = $this->executeDeleteStaff($shop, $details);
                    break;
                case 'query_stats':
                default:
                    $executedResults[] = $this->executeQueryStats($shop);
                    break;
            }
        }

        return $executedResults;
    }

    protected function executeDeleteBookings(BarberShop $shop, array $details): array
    {
        $count = max(1, (int) ($details['count'] ?? 5));
        $ids = $details['ids'] ?? [];

        $query = Booking::where('barber_shop_id', $shop->id);

        if (!empty($ids)) {
            $bookings = $query->whereIn('id', $ids)->get();
        } else {
            $bookings = $query->latest('id')->take($count)->get();
        }

        $deletedLog = [];
        foreach ($bookings as $b) {
            $info = "#{$b->id} - {$b->customer?->name} ({$b->appointment_at?->format('d/m/Y H:i')})";
            AuditTrail::logAi($b, 'delete', 'Bookings');
            $b->delete();
            $deletedLog[] = $info;
        }

        return [
            'title' => "Fshirja e " . count($deletedLog) . " Rezervimeve të Fundit",
            'badge' => 'Fshirje AI',
            'icon' => '🗑️',
            'items' => $deletedLog,
            'audit' => "🤖 [AI Assistant] Rezervimet e zgjedhura u fshinë nga dyqani '{$shop->name}' dhe u regjistruan te Audit Trails."
        ];
    }

    protected function executeDeleteServices(BarberShop $shop, array $details): array
    {
        $count = max(1, (int) ($details['count'] ?? 5));
        $services = Service::where('barber_shop_id', $shop->id)->latest('id')->take($count)->get();

        $deletedLog = [];
        foreach ($services as $s) {
            $info = "#{$s->id} - {$s->name} (" . format_currency($s->price, $shop) . ")";
            AuditTrail::logAi($s, 'delete', 'Services');
            $s->delete();
            $deletedLog[] = $info;
        }

        return [
            'title' => "Fshirja e " . count($deletedLog) . " Shërbimeve të Fundit",
            'badge' => 'Fshirje AI',
            'icon' => '🗑️',
            'items' => $deletedLog,
            'audit' => "🤖 [AI Assistant] U fshinë shërbimet nga dyqani '{$shop->name}' dhe u regjistruan te Audit Trails."
        ];
    }

    protected function executeDeleteStaff(BarberShop $shop, array $details): array
    {
        $count = max(1, (int) ($details['count'] ?? 3));
        $barbers = Barber::where('barber_shop_id', $shop->id)->latest('id')->take($count)->get();

        $deletedLog = [];
        foreach ($barbers as $b) {
            $info = "#{$b->id} - {$b->name}";
            AuditTrail::logAi($b, 'delete', 'Barbers');
            $b->delete();
            $deletedLog[] = $info;
        }

        return [
            'title' => "Fshirja e " . count($deletedLog) . " Pjesëtarëve të Stafit",
            'badge' => 'Fshirje AI',
            'icon' => '🗑️',
            'items' => $deletedLog,
            'audit' => "🤖 [AI Assistant] U fshinë anëtarët e stafit nga dyqani '{$shop->name}' dhe u regjistruan te Audit Trails."
        ];
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

            AuditTrail::logAi($service, 'create', 'Services');
            $created[] = "#{$service->id} - {$service->name} (" . format_currency($service->price, $shop) . ", {$service->duration_minutes}min)";
        }

        return [
            'title' => "Krijimi i " . count($created) . " Shërbimeve të Reja",
            'badge' => 'Shërbime AI',
            'icon' => '✂️',
            'items' => $created,
            'audit' => "🤖 [AI Assistant] Regjistruar te Audit Trails per dyqanin '{$shop->name}'."
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
            AuditTrail::logAi($barber, 'create', 'Barbers');
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
            AuditTrail::logAi($service, 'create', 'Services');
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
            AuditTrail::logAi($customer, 'create', 'Customers');

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

            AuditTrail::logAi($booking, 'create', 'Bookings');
            $createdBookings[] = "#{$booking->id} - {$customer->name} ({$barber->name}) në orën {$appointmentAt->format('H:i')} - " . format_currency($booking->total_price, $shop);
        }

        return [
            'title' => "Krijimi i {$count} Rezervimeve Walk-In për Sot (Pa SMS)",
            'badge' => 'Rezervime AI',
            'icon' => '📅',
            'items' => $createdBookings,
            'audit' => "🤖 [AI Assistant] Krijuar te dyqani '{$shop->name}' dhe regjistruar te Audit Trails."
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

            AuditTrail::logAi($barber, 'create', 'Barbers');
            $created[] = "#{$barber->id} - {$barber->name}";
        }

        return [
            'title' => "Shtimi i " . count($created) . " Pjesëtarëve të Stafit",
            'badge' => 'Staf AI',
            'icon' => '✂️',
            'items' => $created,
            'audit' => "🤖 [AI Assistant] Regjistruar te Audit Trails per dyqanin '{$shop->name}'."
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

            AuditTrail::logAi($customer, 'create', 'Customers');
            $created[] = "#{$customer->id} - {$customer->name}";
        }

        return [
            'title' => "Shtimi i " . count($created) . " Klientëve të Rinj",
            'badge' => 'Klientë AI',
            'icon' => '👤',
            'items' => $created,
            'audit' => "🤖 [AI Assistant] Regjistruar te Audit Trails per dyqanin '{$shop->name}'."
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
            'badge' => 'Analizë AI',
            'icon' => '📊',
            'items' => [
                "Rezervime Sot: {$todayBookings} Takime",
                "Xhiro e Parashikuar Sot: " . format_currency($totalRevenueToday, $shop),
                "Stafi Aktiv: {$totalStaff} " . $shop->resolved_staff_label_plural,
                "Shërbime të Aktivizuara: {$totalServices} Opsione",
            ],
            'audit' => "Query e kryer në kohë reale te dyqani '{$shop->name}'."
        ];
    }
}
