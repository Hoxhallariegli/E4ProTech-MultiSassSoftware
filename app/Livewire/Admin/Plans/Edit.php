<?php

namespace App\Livewire\Admin\Plans;

use App\Models\Plan;
use App\Domain\Plan\DTOs\PlanDTO;
use App\Domain\Plan\Actions\UpdatePlanAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit Plan')]
class Edit extends Component
{
        use WithPagination;
 public Plan $item;
    public $name = '';
    public $price = '';
    public $duration_months = '';
    public $max_barbers = '';
    public $max_services = '';
    public $max_shops = '';
    public $active = false;
   
    public function mount(Plan $plan) { $this->item = $plan; $this->fill($plan->toArray());  }
    public function render() { abort_if_cannot('edit_plans'); return view('livewire.admin.plans.edit', [
        ])->layout('components.layouts.app'); }
    public function update(UpdatePlanAction $action) { $this->validate();  $dto = PlanDTO::fromArray([
            'name' => $this->name,
            'price' => $this->price,
            'duration_months' => $this->duration_months,
            'max_barbers' => $this->max_barbers,
            'max_services' => $this->max_services,
            'max_shops' => $this->max_shops,
            'active' => $this->active,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('plans.updated')); return to_route('admin.plans.index'); }
    protected function rules(): array { $rules = Plan::rules($this->item->id);
        return $rules; }
}