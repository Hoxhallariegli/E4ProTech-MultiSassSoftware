<?php

namespace App\Livewire\Admin\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use App\Models\Booking;
use App\Models\Barber;
use App\Models\BarberShop;
use Carbon\Carbon;

#[Title('Raportet & Statistikat')]
class Reports extends Component
{
    use WithPagination;

    #[Url(history: true)] public $barber_shop_id = '';
    #[Url(history: true)] public $barber_id = '';
    #[Url(history: true)] public $period_type = 'daily';
    #[Url(history: true)] public $report_date = '';
    #[Url(history: true)] public string $search = '';
    public bool $openFilter = false;

    public int $paginate = 15;

    public function mount()
    {
        if (auth()->check() && auth()->user()->barber_shop_id && !auth()->user()->is_global_admin) {
            $this->barber_shop_id = auth()->user()->barber_shop_id;
        }

        if (empty($this->report_date)) {
            $this->report_date = date('Y-m-d');
        }
    }

    public function setPeriod($period)
    {
        if (in_array($period, ['daily', 'weekly', 'monthly'], true)) {
            $this->period_type = $period;
            $this->report_date = date('Y-m-d');
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'barber_id', 'period_type', 'report_date']);
        $this->period_type = 'daily';
        $this->report_date = date('Y-m-d');
        $this->resetPage();
    }

    public function getBarbersProperty()
    {
        $query = Barber::query();
        $shopId = $this->barber_shop_id ?: auth()->user()?->barber_shop_id;
        if ($shopId) {
            $query->where('barber_shop_id', $shopId);
        }
        return $query->pluck('name', 'id')->toArray();
    }

    public function getBarberShopsProperty()
    {
        return BarberShop::pluck('name', 'id')->toArray();
    }

    public function render()
    {
        abort_if_cannot('view_reports');

        $shopId = $this->barber_shop_id ?: auth()->user()?->barber_shop_id;
        $dateStr = $this->report_date ?: date('Y-m-d');
        $period = $this->period_type ?: 'daily';

        $startDate = Carbon::parse($dateStr)->startOfDay();
        $endDate = Carbon::parse($dateStr)->endOfDay();

        if ($period === 'weekly') {
            $startDate = Carbon::parse($dateStr)->startOfWeek();
            $endDate = Carbon::parse($dateStr)->endOfWeek();
        } elseif ($period === 'monthly') {
            $startDate = Carbon::parse($dateStr)->startOfMonth();
            $endDate = Carbon::parse($dateStr)->endOfMonth();
        }

        // Base query for bookings
        $baseQuery = Booking::query();

        if ($shopId) {
            $baseQuery->where('barber_shop_id', $shopId);
        }

        if ($this->barber_id) {
            $baseQuery->where('barber_id', $this->barber_id);
        }

        $periodQuery = (clone $baseQuery)->whereBetween('appointment_at', [$startDate, $endDate]);

        // Calculate KPI Metrics in real-time
        $allPeriodBookings = (clone $periodQuery)->get();

        $totalBookings = $allPeriodBookings->count();
        $totalRevenue = (float) $allPeriodBookings->filter(function($b) {
            return $b->status !== 'cancelled' && (
                in_array($b->status, ['completed', 'paid', 'confirmed'], true)
                || ($b->payment_status ?? '') === 'paid'
                || ($b->payment?->status ?? '') === 'paid'
            );
        })->sum('total_price');
        $totalCompleted = $allPeriodBookings->where('status', 'completed')->count();
        $totalCancelled = $allPeriodBookings->where('status', 'cancelled')->count();

        // Staff Performance Breakdown for the selected period
        $barberBreakdown = (clone $periodQuery)
            ->with('barber')
            ->get()
            ->groupBy('barber_id')
            ->map(function ($group) use ($totalRevenue) {
                $barberName = $group->first()?->barber?->name ?? 'Staf i Papërcaktuar';
                $completed = $group->where('status', 'completed');
                $revenue = (float) $completed->sum('total_price');
                $count = $group->count();
                $share = $totalRevenue > 0 ? ($revenue / $totalRevenue) * 100 : 0;
                return [
                    'barber_name' => $barberName,
                    'total_bookings' => $count,
                    'completed_bookings' => $completed->count(),
                    'revenue' => $revenue,
                    'share' => round($share, 1),
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        // Paginated list of bookings for the breakdown table
        $tableQuery = (clone $periodQuery)->with(['barber', 'service', 'customer', 'barberShop']);

        if (!empty($this->search)) {
            $search = $this->search;
            $tableQuery->where(function ($q) use ($search) {
                $q->whereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
                  ->orWhereHas('service', fn ($s) => $s->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('barber', fn ($b) => $b->where('name', 'like', "%{$search}%"));
            });
        }

        $bookingsList = $tableQuery->orderBy('appointment_at', 'desc')->paginate($this->paginate);

        return view('livewire.admin.reports.index', [
            'totalBookings' => $totalBookings,
            'totalRevenue' => $totalRevenue,
            'totalCompleted' => $totalCompleted,
            'totalCancelled' => $totalCancelled,
            'barberBreakdown' => $barberBreakdown,
            'bookings' => $bookingsList,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'barbers' => $this->barbers,
            'barberShops' => $this->barberShops,
        ])->layout('components.layouts.app');
    }
}
