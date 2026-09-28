<?php

namespace App\Livewire\Admin\WorkingHours;

use App\Models\WorkingHour;
use App\Domain\WorkingHour\DTOs\WorkingHourDTO;
use App\Domain\WorkingHour\Actions\CreateWorkingHourAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Add WorkingHour')]
class Create extends Component
{
        use WithPagination;
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

    public function render() { abort_if_cannot('add_working_hours'); return view('livewire.admin.working-hours.create', [
            'barbers' => $this->getbarbersList(),
        ])->layout('components.layouts.app'); }
    public function store(CreateWorkingHourAction $action) { $this->validate();  $dto = WorkingHourDTO::fromArray([
            'barber_id' => $this->barber_id,
            'day_of_week' => $this->day_of_week,
            'open_time' => $this->open_time,
            'close_time' => $this->close_time,
            'is_closed' => $this->is_closed,
        ]); $action->execute($dto); session()->flash('success', __('working-hours.created')); return to_route('admin.working-hours.index'); }
    protected function rules(): array { $rules = WorkingHour::rules();
        return $rules; }
}
