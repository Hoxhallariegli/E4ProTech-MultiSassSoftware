<?php

namespace App\Livewire\Admin;

use App\Services\GroqService;
use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\BarberShop;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\AuditTrail;
use Carbon\Carbon;
use Illuminate\Support\Str;

#[Title('AI Assistant')]
class AiAssistant extends Component
{
    public string $prompt = '';
    public bool $isProcessing = false;
    public array $history = [];

    public function process()
    {
        $this->validate(['prompt' => 'required|min:2']);
        $this->isProcessing = true;

        try {
            $user = auth()->user();
            $shop = $user?->barberShop;

            if (!$shop && $user?->is_global_admin) {
                $shop = BarberShop::first();
            }

            if (!$shop) {
                throw new \Exception('Nuk u gjet asnjë sallon aktiv për të kryer këtë veprim.');
            }

            $ai = new GroqService();
            $actions = $ai->interpretOperationalPrompt($this->prompt);

            $executedResults = [];

            foreach ($actions as $action) {
                $type = $action['action'] ?? '';
                $details = $action['details'] ?? [];

                switch ($type) {
                    case 'create_services':
                        $res = $this->executeCreateServices($shop, $details);
                        $executedResults[] = $res;
                        break;

                    case 'create_bookings':
                        $res = $this->executeCreateBookings($shop, $user, $details);
                        $executedResults[] = $res;
                        break;

                    case 'create_staff':
                        $res = $this->executeCreateStaff($shop, $user, $details);
                        $executedResults[] = $res;
                        break;

                    case 'create_customers':
                        $res = $this->executeCreateCustomers($shop, $details);
                        $executedResults[] = $res;
                        break;

                    case 'query_stats':
                    default:
                        $res = $this->executeQueryStats($shop);
                        $executedResults[] = $res;
                        break;
                }
            }

            $this->history[] = [
                'type' => 'success',
                'user_prompt' => $this->prompt,
                'time' => now()->format('H:i:s'),
                'results' => $executedResults,
            ];

            $this->prompt = '';
            $this->dispatch('toast', message: 'Kërkesa u ekzekutua me sukses! Të dhënat u regjistruan te Audit Trails. 🚀', type: 'success');

        } catch (\Exception $e) {
            $this->history[] = [
                'type' => 'error',
                'user_prompt' => $this->prompt,
                'time' => now()->format('H:i:s'),
                'message' => $e->getMessage()
            ];
        }

        $this->isProcessing = false;
    }

    protected function executeCreateServices(BarberShop $shop, array $details): array
    {
        $servicesList = $details['services'] ?? [];
        $created = [];

        foreach ($servicesList as $item) {
            $name = trim($item['name'] ?? 'Shërbim AI');
            $price = (float) ($item['price'] ?? 1000);
            $duration = (int) ($item['duration_minutes'] ?? 30);

            $service = Service::create([
                'barber_shop_id' => $shop->id,
                'name' => $name,
                'description' => 'Shërbim i krijuar automatikisht nga AI Assistant',
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
            'icon' => '✂️',
            'items' => $created,
            'audit' => "Të gjitha shërbimet u regjistruan te Audit Trails (Section: Services)."
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
            $service = Service::create([
                'barber_shop_id' => $shop->id,
                'name' => 'Prerje & Stilim',
                'price' => 1000,
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
                'notes' => 'Walk-In • Krijuar automatikisht nga AI Assistant (Pa SMS)',
                'source' => 'walk-in',
            ]);

            AuditTrail::log($booking, 'create', 'Bookings');
            $createdBookings[] = "#{$booking->id} - {$customer->name} ({$barber->name}) në orën {$appointmentAt->format('H:i')} - " . format_currency($booking->total_price, $shop);
        }

        return [
            'title' => "Krijimi i {$count} Rezervimeve Walk-In për Sot (Pa SMS)",
            'badge' => 'Rezervime',
            'icon' => '📅',
            'items' => $createdBookings,
            'audit' => "Krijuar pa dërgim mesazhesh SMS. U regjistruan te Audit Trails (Section: Bookings)."
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
            'icon' => '✂️',
            'items' => $created,
            'audit' => "Regjistruar te Audit Trails (Section: Barbers)."
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
            'icon' => '👤',
            'items' => $created,
            'audit' => "Regjistruar te Audit Trails (Section: Customers)."
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
            'icon' => '📊',
            'items' => [
                "Rezervime Sot: {$todayBookings} Takime",
                "Xhiro e Parashikuar Sot: " . format_currency($totalRevenueToday, $shop),
                "Stafi Aktiv: {$totalStaff} " . $shop->resolved_staff_label_plural,
                "Shërbime të Aktivizuara: {$totalServices} Opsione",
            ],
            'audit' => "Query e kryer në kohë reale nga AI Assistant."
        ];
    }

    public function render()
    {
        return view('livewire.admin.ai-assistant')->layout('components.layouts.app');
    }
}
