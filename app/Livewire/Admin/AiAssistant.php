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
use App\Models\AiConversation;
use Carbon\Carbon;
use Illuminate\Support\Str;

#[Title('AI Assistant')]
class AiAssistant extends Component
{
    public string $prompt = '';
    public bool $isProcessing = false;
    public array $history = [];

    // History Pagination State
    public int $historyLimit = 5;
    public int $totalHistoryCount = 0;

    // Confirmation State
    public bool $showConfirmation = false;
    public string $pendingPrompt = '';
    public array $pendingActions = [];
    public array $previewItems = [];

    public function mount()
    {
        $this->loadHistory();
        $this->dispatch('scroll-bottom');
    }

    public function loadHistory()
    {
        $user = auth()->user();
        $shop = $user?->barberShop ?: BarberShop::first();

        if ($shop && $user) {
            $query = AiConversation::where('barber_shop_id', $shop->id)
                ->where('user_id', $user->id);

            $this->totalHistoryCount = $query->count();

            $logs = $query->latest('id')
                ->take($this->historyLimit)
                ->get();

            $this->history = $logs->map(function ($log) {
                $promptText = trim((string) $log->user_prompt);
                if (str_starts_with($promptText, '[') || str_starts_with($promptText, '{') || str_contains($promptText, 'Krijimi i')) {
                    $promptText = 'Udhëzim për AI Assistant';
                }

                return [
                    'type' => $log->status,
                    'user_prompt' => $promptText,
                    'time' => $log->created_at->format('H:i:s'),
                    'results' => $log->results,
                    'message' => $log->error_message,
                ];
            })->reverse()->values()->toArray();
        }

        $this->dispatch('scroll-bottom');
    }

    public function loadMoreHistory()
    {
        $this->historyLimit += 5;
        $this->loadHistory();
    }

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

            // Check if there are write/delete actions requiring confirmation
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
                    $count = $details['count'] ?? 1;
                    $custName = !empty($details['customer_name']) ? $details['customer_name'] : 'Klient Rastësor';
                    $startH = isset($details['start_hour']) ? (int)$details['start_hour'] : 13;
                    $startM = isset($details['start_minute']) ? (int)$details['start_minute'] : 0;
                    $timeStr = sprintf('%02d:%02d', $startH, $startM);

                    $previews[] = [
                        'title' => "Krijimi i {$count} Rezervimeve Walk-In për Sot (Pa SMS)",
                        'icon' => '📅',
                        'badge' => 'Rezervime',
                        'items' => ["{$count} Takime sot për {$custName} në orën {$timeStr}"]
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

                    $query = Booking::with(['customer', 'service', 'barber'])->where('barber_shop_id', $shop->id);
                    if (!empty($ids)) {
                        $targetBookings = $query->whereIn('id', $ids)->get();
                    } else {
                        $targetBookings = $query->latest('id')->take($count)->get();
                    }

                    if ($targetBookings->isNotEmpty()) {
                        $itemsList = $targetBookings->map(fn($b) => "Fshirja e Rezervimit #{$b->id} - " . ($b->customer?->name ?? 'Klient') . " (" . ($b->service?->name ?? 'Shërbim') . ", " . format_currency($b->total_price, $shop) . ", " . ($b->appointment_at?->format('d/m/Y H:i') ?? '') . ")")->toArray();
                    } else {
                        $itemsList = ["Asnjë rezervim nuk u gjet te dyqani '{$shop->name}' për t'u fshirë."];
                    }

                    $previews[] = [
                        'title' => "Fshirja e " . count($targetBookings) . " Rezervimeve të Fundit",
                        'icon' => '🗑️',
                        'badge' => 'Fshirje',
                        'items' => $itemsList
                    ];
                } elseif ($type === 'delete_services') {
                    $requiresConfirmation = true;
                    $count = $details['count'] ?? 5;

                    $targetServices = Service::where('barber_shop_id', $shop->id)->latest('id')->take($count)->get();

                    if ($targetServices->isNotEmpty()) {
                        $itemsList = $targetServices->map(fn($s) => "Fshirja e Shërbimit #{$s->id} - {$s->name} (" . format_currency($s->price, $shop) . ", {$s->duration_minutes}min)")->toArray();
                    } else {
                        $itemsList = ["Asnjë shërbim nuk u gjet te dyqani '{$shop->name}' për t'u fshirë."];
                    }

                    $previews[] = [
                        'title' => "Fshirja e " . count($targetServices) . " Shërbimeve të Fundit",
                        'icon' => '🗑️',
                        'badge' => 'Fshirje',
                        'items' => $itemsList
                    ];
                } elseif ($type === 'delete_staff') {
                    $requiresConfirmation = true;
                    $count = $details['count'] ?? 3;

                    $targetBarbers = Barber::where('barber_shop_id', $shop->id)->latest('id')->take($count)->get();

                    if ($targetBarbers->isNotEmpty()) {
                        $itemsList = $targetBarbers->map(fn($b) => "Fshirja e Stafit #{$b->id} - {$b->name}")->toArray();
                    } else {
                        $itemsList = ["Asnjë pjesëtar stafi nuk u gjet te dyqani '{$shop->name}' për t'u fshirë."];
                    }

                    $previews[] = [
                        'title' => "Fshirja e " . count($targetBarbers) . " Pjesëtarëve të Stafit",
                        'icon' => '🗑️',
                        'badge' => 'Fshirje',
                        'items' => $itemsList
                    ];
                } elseif ($type === 'query_stats') {
                    $res = $this->executeQueryStats($shop);
                    $promptText = trim($this->prompt);

                    AiConversation::create([
                        'barber_shop_id' => $shop->id,
                        'user_id' => $user->id,
                        'user_prompt' => $promptText,
                        'status' => 'success',
                        'results' => [$res],
                    ]);

                    $this->loadHistory();
                    $this->prompt = '';
                    $this->isProcessing = false;
                    $this->dispatch('scroll-bottom');
                    return;
                }
            }

            if ($requiresConfirmation) {
                $this->pendingPrompt = trim($this->prompt);
                $this->pendingActions = $actions;
                $this->previewItems = $previews;
                $this->showConfirmation = true;
                $this->prompt = '';
            } else {
                $this->executeActions($shop, $user, $actions);
            }

        } catch (\Exception $e) {
            if (isset($shop) && isset($user)) {
                AiConversation::create([
                    'barber_shop_id' => $shop->id,
                    'user_id' => $user->id,
                    'user_prompt' => trim($this->prompt),
                    'status' => 'error',
                    'error_message' => $e->getMessage(),
                ]);
            }
            $this->loadHistory();
        }

        $this->isProcessing = false;
        $this->dispatch('scroll-bottom');
    }

    public function confirmExecution()
    {
        if (!$this->showConfirmation || empty($this->pendingActions)) {
            return;
        }

        try {
            $user = auth()->user();
            $shop = $user?->barberShop ?: BarberShop::first();

            $this->executeActions($shop, $user, $this->pendingActions);

            $this->showConfirmation = false;
            $this->pendingActions = [];
            $this->previewItems = [];
            $this->pendingPrompt = '';

            $this->dispatch('toast', message: 'Të dhënat u regjistruan me sukses te Baza e Të Dhënave & Audit Trails! 🚀', type: 'success');
            $this->dispatch('scroll-bottom');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Gabim gjatë ekzekutimit: ' . $e->getMessage(), type: 'error');
        }
    }

    public function cancelExecution()
    {
        $user = auth()->user();
        $shop = $user?->barberShop ?: BarberShop::first();

        if ($shop && $user) {
            AiConversation::create([
                'barber_shop_id' => $shop->id,
                'user_id' => $user->id,
                'user_prompt' => trim($this->pendingPrompt ?: 'Udhëzim i anuluar'),
                'status' => 'info',
                'error_message' => 'Kërkesa u anulua nga përdoruesi. Asnjë e dhënë nuk u ndryshua.',
            ]);
        }

        $this->showConfirmation = false;
        $this->pendingActions = [];
        $this->previewItems = [];
        $this->pendingPrompt = '';

        $this->loadHistory();
        $this->dispatch('toast', message: 'Kërkesa u anulua me sukses.', type: 'info');
        $this->dispatch('scroll-bottom');
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

        $promptText = trim($this->pendingPrompt ?: $this->prompt);
        if (empty($promptText) || str_starts_with($promptText, '[') || str_starts_with($promptText, '{')) {
            $promptText = 'Udhëzim për AI Assistant';
        }

        AiConversation::create([
            'barber_shop_id' => $shop->id,
            'user_id' => $user->id,
            'user_prompt' => $promptText,
            'status' => 'success',
            'results' => $executedResults,
        ]);

        $this->loadHistory();
        $this->dispatch('scroll-bottom');
    }

    protected function executeDeleteBookings(BarberShop $shop, array $details): array
    {
        $count = max(1, (int) ($details['count'] ?? 5));
        $ids = $details['ids'] ?? [];

        $query = Booking::with(['customer', 'service', 'barber'])->where('barber_shop_id', $shop->id);

        if (!empty($ids)) {
            $bookings = $query->whereIn('id', $ids)->get();
        } else {
            $bookings = $query->latest('id')->take($count)->get();
        }

        $deletedLog = [];
        foreach ($bookings as $b) {
            $info = "#{$b->id} - " . ($b->customer?->name ?? 'Klient') . " (" . ($b->appointment_at?->format('d/m/Y H:i') ?? '') . ")";
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
                'description' => 'Shërbim i krijuar automatikisht nga AI Assistant',
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
        $count = max(1, (int) ($details['count'] ?? 1));
        $startHour = isset($details['start_hour']) ? (int) $details['start_hour'] : 13;
        $startMin = isset($details['start_minute']) ? (int) $details['start_minute'] : 0;
        $endHour = isset($details['end_hour']) ? (int) $details['end_hour'] : ($startHour + 4);
        $specifiedCustomerName = !empty($details['customer_name']) ? trim($details['customer_name']) : null;

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
        $hourStep = max(1, (int) ceil(($endHour - $startHour) / max(1, $count)));

        $createdBookings = [];
        $sampleNames = ['Alban Hoxha', 'Erion Marku', 'Sokol Prifti', 'Gentian Krasniqi', 'Dritan Basha', 'Andi Topalli', 'Klodian Vaso'];

        for ($i = 0; $i < $count; $i++) {
            if ($specifiedCustomerName) {
                $custName = $specifiedCustomerName;
            } else {
                $custName = $sampleNames[$i % count($sampleNames)] . " (Walk-In)";
            }

            $customer = Customer::firstOrCreate(
                ['barber_shop_id' => $shop->id, 'name' => mb_convert_case($custName, MB_CASE_TITLE, 'UTF-8')],
                ['phone' => '+35569' . rand(1000000, 9999999)]
            );
            AuditTrail::logAi($customer, 'create', 'Customers');

            if ($count === 1) {
                $hour = $startHour;
                $min = $startMin;
            } else {
                $hour = min($endHour - 1, $startHour + ($i * $hourStep));
                $min = [0, 15, 30, 45][rand(0, 3)];
            }

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

    public function render()
    {
        return view('livewire.admin.ai-assistant')->layout('components.layouts.app');
    }
}
