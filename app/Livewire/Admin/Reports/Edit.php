<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Report;
use App\Domain\Report\DTOs\ReportDTO;
use App\Domain\Report\Actions\UpdateReportAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit Report')]
class Edit extends Component
{
        use WithPagination;
 public Report $item;
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

    public function mount(Report $report) { $this->item = $report; $this->fill($report->toArray()); $this->report_date = $report->report_date?->format('Y-m-d'); }
    public function render() { abort_if_cannot('edit_reports'); return view('livewire.admin.reports.edit', [
            'barbers' => $this->getbarbersList(),
            'barberShops' => $this->getbarberShopsList(),
        ])->layout('components.layouts.app'); }
    public function update(UpdateReportAction $action) { $this->validate();  $dto = ReportDTO::fromArray([
            'period_type' => $this->period_type,
            'report_date' => $this->report_date,
            'total_bookings' => $this->total_bookings,
            'total_revenue' => $this->total_revenue,
            'total_completed' => $this->total_completed,
            'total_cancelled' => $this->total_cancelled,
            'notes' => $this->notes,
            'barber_id' => $this->barber_id,
            'barber_shop_id' => $this->barber_shop_id,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('reports.updated')); return to_route('admin.reports.index'); }
    protected function rules(): array { $rules = Report::rules($this->item->id);
        return $rules; }
}