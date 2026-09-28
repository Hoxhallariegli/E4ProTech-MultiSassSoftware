<?php

namespace App\Livewire\Admin\MessageQueues;

use App\Models\MessageQueue;
use App\Domain\MessageQueue\DTOs\MessageQueueDTO;
use App\Domain\MessageQueue\Actions\CreateMessageQueueAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class QuickCreate extends Component
{
        use WithPagination;
     public $barber_shop_id = '';
    public $booking_id = '';
    public $channel = '';
    public $phone_number = '';
    public $message_content = '';
    public $scheduled_at = '';
    public $status = '';
    public $retry_count = '';
 
    #[On('barber-shop-created')] 
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }

    #[On('booking-created')] 
    public function refreshBookings($id) { $this->booking_id = $id; $this->updatedBookingId($id); }
 
    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
        if (isset($related->booking_id)) { $this->booking_id = $related->booking_id; }
    }

    public function updatedBookingId($value)
    {
        if (!$value) return;
        $related = \App\Models\Booking::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id)) { $this->barber_shop_id = $related->barber_shop_id; }
    }
 
    protected function getbarberShopsList() {
        $query = \App\Models\BarberShop::query();
        if (method_exists(\App\Models\BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    protected function getbookingsList() {
        $query = \App\Models\Booking::query();
        if (method_exists(\App\Models\Booking::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->with('customer')->get()->pluck('customer.name', 'id')->toArray();
    }

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.message-queues.quick-create', [
            'barberShops' => $this->getbarberShopsList(),
            'bookings' => $this->getbookingsList(),
        ]); }

    public function store(CreateMessageQueueAction $action)
    {
        $this->validate();
        $dto = MessageQueueDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'booking_id' => $this->booking_id,
            'channel' => $this->channel,
            'phone_number' => $this->phone_number,
            'message_content' => $this->message_content,
            'scheduled_at' => $this->scheduled_at,
            'status' => $this->status,
            'retry_count' => $this->retry_count,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('message-queue-created', id: $item->id);
        $this->js("Livewire.dispatch('message-queue-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('message-queues.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['barber_shop_id', 'booking_id', 'channel', 'phone_number', 'message_content', 'scheduled_at', 'status', 'retry_count']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = MessageQueue::rules();
        return $rules; }
}