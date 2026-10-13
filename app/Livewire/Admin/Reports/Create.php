<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Report;
use App\Domain\Report\DTOs\ReportDTO;
use App\Domain\Report\Actions\CreateReportAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Krijo Raport të Ri')]
class Create extends Component
{
    use WithPagination;

    public $period_type = 'daily';
    public $report_date = '';
    public $total_bookings = 0;
    public $total_revenue = 0.0;
    public $total_completed = 0;
    public $total_cancelled = 0;
    public $notes = '';
    public $barber_id = '';
    public $barber_shop_id = '';

    #[On('barber-created')]
    public function refreshBarbers($id) { $this->barber_id = $id; $this->updatedBarberId($id); }

    #[On('barber-shop-created')]
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }

    public function updatedBarberId($value)
    {
        if (!$value) return;
        $related = \App\Models\Barber::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id)) { $this->barber_shop_id = $related->barber_shop_id; }
        $this->calculateReportMetrics();
    }

    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
        if (isset($related->barber_id)) { $this->barber_id = $related->barber_id; }
        $this->calculateReportMetrics();
    }

    public function updatedPeriodType($value)
    {
        $this->calculateReportMetrics();
    }

    public function updatedReportDate($value)
    {
        $this->calculateReportMetrics();
    }

    public function calculateReportMetrics()
    {
        $shopId = $this->barber_shop_id ?: (auth()->user()?->barber_shop_id);
        if (!$shopId) {
            $this->dispatch('toast', message: 'Zgjidhni më parë dyqanin/sallonin.', type: 'error');
            return;
        }

        $dateStr = $this->report_date ?: date('Y-m-d');
        $this->report_date = $dateStr;
        $period = $this->period_type ?: 'daily';
        $this->period_type = $period;

        $query = \App\Models\Booking::where('barber_shop_id', $shopId);

        if ($this->barber_id) {
            $query->where('barber_id', $this->barber_id);
        }

        $startDate = \Carbon\Carbon::parse($dateStr)->startOfDay();
        $endDate = \Carbon\Carbon::parse($dateStr)->endOfDay();

        if ($period === 'weekly') {
            $startDate = \Carbon\Carbon::parse($dateStr)->startOfWeek();
            $endDate = \Carbon\Carbon::parse($dateStr)->endOfWeek();
        } elseif ($period === 'monthly') {
            $startDate = \Carbon\Carbon::parse($dateStr)->startOfMonth();
            $endDate = \Carbon\Carbon::parse($dateStr)->endOfMonth();
        }

        $bookings = $query->whereBetween('appointment_at', [$startDate, $endDate])->get();

        $this->total_bookings = $bookings->count();
        $this->total_revenue = number_format($bookings->whereIn('status', ['completed', 'paid', 'confirmed'])->sum('total_price'), 2, '.', '');
        $this->total_completed = $bookings->where('status', 'completed')->count();
        $this->total_cancelled = $bookings->where('status', 'cancelled')->count();

        $barberName = $this->barber_id ? (\App\Models\Barber::find($this->barber_id)?->name ?? 'Stafi') : 'Gjithë Stafi';
        $this->notes = "Kalkuluar automatikisht nga baza e të dhënave për {$barberName} me periudhë {$period} ({$startDate->format('d/m/Y')} - {$endDate->format('d/m/Y')}).";

        $this->dispatch('toast', message: 'Statistikat u kalkuluan automatikisht nga baza e të dhënave! 📊', type: 'success');
    }

    protected function getbarbersList() {
        $query = \App\Models\Barber::query();
        if (method_exists(\App\Models\Barber::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    protected function getbarberShopsList() {
        $query = \App\Models\BarberShop::query();
        if (method_exists(\App\Models\BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    public function mount() {
        if (auth()->check() && auth()->user()->barber_shop_id) {
            $this->barber_shop_id = auth()->user()->barber_shop_id;
        }
        $this->report_date = date('Y-m-d');
        $this->period_type = 'daily';
        $this->calculateReportMetrics();
    }

    public function render() {
        abort_if_cannot('add_reports');
        return view('livewire.admin.reports.create', [
            'barbers' => $this->getbarbersList(),
            'barberShops' => $this->getbarberShopsList(),
        ])->layout('components.layouts.app');
    }

    public function store(CreateReportAction $action) {
        $this->validate();
        $dto = ReportDTO::fromArray([
            'period_type' => $this->period_type,
            'report_date' => $this->report_date,
            'total_bookings' => $this->total_bookings,
            'total_revenue' => $this->total_revenue,
            'total_completed' => $this->total_completed,
            'total_cancelled' => $this->total_cancelled,
            'notes' => $this->notes,
            'barber_id' => $this->barber_id,
            'barber_shop_id' => $this->barber_shop_id,
        ]);
        $action->execute($dto);
        session()->flash('success', __('reports.created'));
        return to_route('admin.reports.index');
    }

    protected function rules(): array {
        return Report::rules();
    }
}
