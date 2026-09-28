<?php

namespace App\Livewire\Admin\MessageQueues;

use App\Models\MessageQueue;
use App\Domain\MessageQueue\DTOs\MessageQueueDTO;
use App\Domain\MessageQueue\Actions\UpdateMessageQueueAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit MessageQueue')]
class Edit extends Component
{
        use WithPagination;
 public MessageQueue $item;
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

    public function mount(MessageQueue $messageQueue) { $this->item = $messageQueue; $this->fill($messageQueue->toArray()); $this->scheduled_at = $messageQueue->scheduled_at?->format('Y-m-d\TH:i'); }
    public function render() { abort_if_cannot('edit_message_queues'); return view('livewire.admin.message-queues.edit', [
            'barberShops' => $this->getbarberShopsList(),
            'bookings' => $this->getbookingsList(),
        ])->layout('components.layouts.app'); }
    public function update(UpdateMessageQueueAction $action) { $this->validate();  $dto = MessageQueueDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'booking_id' => $this->booking_id,
            'channel' => $this->channel,
            'phone_number' => $this->phone_number,
            'message_content' => $this->message_content,
            'scheduled_at' => $this->scheduled_at,
            'status' => $this->status,
            'retry_count' => $this->retry_count,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('message-queues.updated')); return to_route('admin.message-queues.index'); }
    protected function rules(): array { $rules = MessageQueue::rules($this->item->id);
        return $rules; }
}