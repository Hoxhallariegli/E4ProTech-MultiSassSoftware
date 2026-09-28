<?php

namespace App\Livewire\Admin\MessageLogs;

use App\Models\MessageLog;
use App\Domain\MessageLog\DTOs\MessageLogDTO;
use App\Domain\MessageLog\Actions\CreateMessageLogAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Add MessageLog')]
class Create extends Component
{
        use WithPagination;
     public $barber_shop_id = '';
    public $customer_id = '';
    public $channel = '';
    public $message = '';
    public $status = '';
    public $sent_at = '';
 
    #[On('barber-shop-created')] 
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }

    #[On('customer-created')] 
    public function refreshCustomers($id) { $this->customer_id = $id; $this->updatedCustomerId($id); }
 
    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
        if (isset($related->customer_id)) { $this->customer_id = $related->customer_id; }
    }

    public function updatedCustomerId($value)
    {
        if (!$value) return;
        $related = \App\Models\Customer::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id)) { $this->barber_shop_id = $related->barber_shop_id; }
    }
 
    protected function getbarberShopsList() {
        $query = \App\Models\BarberShop::query();
        if (method_exists(\App\Models\BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    protected function getcustomersList() {
        $query = \App\Models\Customer::query();
        if (method_exists(\App\Models\Customer::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }
 
    public function mount() { if (auth()->check() && auth()->user()->barber_shop_id) { $this->barber_shop_id = auth()->user()->barber_shop_id; } }

    public function render() { abort_if_cannot('add_message_logs'); return view('livewire.admin.message-logs.create', [
            'barberShops' => $this->getbarberShopsList(),
            'customers' => $this->getcustomersList(),
        ])->layout('components.layouts.app'); }
    public function store(CreateMessageLogAction $action) { $this->validate();  $dto = MessageLogDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'customer_id' => $this->customer_id,
            'channel' => $this->channel,
            'message' => $this->message,
            'status' => $this->status,
            'sent_at' => $this->sent_at,
        ]); $action->execute($dto); session()->flash('success', __('message-logs.created')); return to_route('admin.message-logs.index'); }
    protected function rules(): array { $rules = MessageLog::rules();
        return $rules; }
}