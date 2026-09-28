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

class QuickCreate extends Component
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

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.working-hours.quick-create', [
            'barbers' => $this->getbarbersList(),
        ]); }

    public function store(CreateWorkingHourAction $action)
    {
        $this->validate();
        $dto = WorkingHourDTO::fromArray([
            'barber_id' => $this->barber_id,
            'day_of_week' => $this->day_of_week,
            'open_time' => $this->open_time,
            'close_time' => $this->close_time,
            'is_closed' => $this->is_closed,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('working-hour-created', id: $item->id);
        $this->js("Livewire.dispatch('working-hour-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('working-hours.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['barber_id', 'day_of_week', 'open_time', 'close_time', 'is_closed']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = WorkingHour::rules();
        return $rules; }
}
