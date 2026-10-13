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

class QuickCreate extends Component
{
        use WithPagination;
     public $period_type = '';
    public $report_date = '';
    public $total_bookings = '';
    public $total_revenue = '';
    public $total_completed = '';
    public $total_cancelled = '';
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
    }

    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
        if (isset($related->barber_id)) { $this->barber_id = $related->barber_id; }
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

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.reports.quick-create', [
            'barbers' => $this->getbarbersList(),
            'barberShops' => $this->getbarberShopsList(),
        ]); }

    public function store(CreateReportAction $action)
    {
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
        $item = $action->execute($dto);
        $this->dispatch('report-created', id: $item->id);
        $this->js("Livewire.dispatch('report-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('reports.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['period_type', 'report_date', 'total_bookings', 'total_revenue', 'total_completed', 'total_cancelled', 'notes', 'barber_id', 'barber_shop_id']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = Report::rules();
        return $rules; }
}