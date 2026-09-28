<?php

namespace App\Livewire\Admin\WorkingHours;

use App\Models\WorkingHour;
use App\Domain\WorkingHour\DTOs\WorkingHourDTO;
use App\Domain\WorkingHour\Actions\UpdateWorkingHourAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit WorkingHour')]
class Edit extends Component
{
        use WithPagination;
 public WorkingHour $item;
    public $barber_id = '';
    public $day_of_week = '';
    public $open_time = '';
    public $close_time = '';
    public $is_closed = false;

    #[On('barber-created')]
    public function refreshBarbers($id) { $this->barber_id = $id; $this->updatedBarberId($id); }

    public function updatedBarberId($value)
    {
        if (!$value) return;
        $related = \App\Models\Barber::find($value);
        if (!$related) return;
    }

    protected function getbarbersList() {
        $query = \App\Models\Barber::query();
        if (method_exists(\App\Models\Barber::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    public function mount(WorkingHour $workingHour) { $this->item = $workingHour; $this->fill($workingHour->toArray()); $this->open_time = $workingHour->open_time?->format('Y-m-d\TH:i'); $this->close_time = $workingHour->close_time?->format('Y-m-d\TH:i'); }
    public function render() { abort_if_cannot('edit_working_hours'); return view('livewire.admin.working-hours.edit', [
            'barbers' => $this->getbarbersList(),
        ])->layout('components.layouts.app'); }
    public function update(UpdateWorkingHourAction $action) { $this->validate();  $dto = WorkingHourDTO::fromArray([
            'barber_id' => $this->barber_id,
            'day_of_week' => $this->day_of_week,
            'open_time' => $this->open_time,
            'close_time' => $this->close_time,
            'is_closed' => $this->is_closed,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('working-hours.updated')); return to_route('admin.working-hours.index'); }
    protected function rules(): array { $rules = WorkingHour::rules($this->item->id);
        return $rules; }
}
