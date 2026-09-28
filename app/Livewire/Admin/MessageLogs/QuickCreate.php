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

class QuickCreate extends Component
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

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.message-logs.quick-create', [
            'barberShops' => $this->getbarberShopsList(),
            'customers' => $this->getcustomersList(),
        ]); }

    public function store(CreateMessageLogAction $action)
    {
        $this->validate();
        $dto = MessageLogDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'customer_id' => $this->customer_id,
            'channel' => $this->channel,
            'message' => $this->message,
            'status' => $this->status,
            'sent_at' => $this->sent_at,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('message-log-created', id: $item->id);
        $this->js("Livewire.dispatch('message-log-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('message-logs.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['barber_shop_id', 'customer_id', 'channel', 'message', 'status', 'sent_at']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = MessageLog::rules();
        return $rules; }
}