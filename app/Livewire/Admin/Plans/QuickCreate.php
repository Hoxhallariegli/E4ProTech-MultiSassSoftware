<?php

namespace App\Livewire\Admin\Plans;

use App\Models\Plan;
use App\Domain\Plan\DTOs\PlanDTO;
use App\Domain\Plan\Actions\CreatePlanAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class QuickCreate extends Component
{
        use WithPagination;
     public $name = '';
    public $price = '';
    public $duration_months = '';
    public $max_barbers = '';
    public $max_services = '';
    public $max_shops = '';
    public $active = false;
   
    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.plans.quick-create', [
        ]); }

    public function store(CreatePlanAction $action)
    {
        $this->validate();
        $dto = PlanDTO::fromArray([
            'name' => $this->name,
            'price' => $this->price,
            'duration_months' => $this->duration_months,
            'max_barbers' => $this->max_barbers,
            'max_services' => $this->max_services,
            'max_shops' => $this->max_shops,
            'active' => $this->active,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('plan-created', id: $item->id);
        $this->js("Livewire.dispatch('plan-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('plans.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->name ?? $item->id);
        $this->reset(['name', 'price', 'duration_months', 'max_barbers', 'max_services', 'max_shops', 'active']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = Plan::rules();
        return $rules; }
}