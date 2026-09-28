<?php

namespace App\Livewire\Admin\NotificationChannels;

use App\Models\NotificationChannel;
use App\Domain\NotificationChannel\DTOs\NotificationChannelDTO;
use App\Domain\NotificationChannel\Actions\UpdateNotificationChannelAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit NotificationChannel')]
class Edit extends Component
{
        use WithPagination;
 public NotificationChannel $item;
    public $barber_shop_id = '';
    public $channel = '';
    public $enabled = false;
    public $daily_limit = '';
 
    #[On('barber-shop-created')] 
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }
 
    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
    }
 
    protected function getbarberShopsList() {
        $query = \App\Models\BarberShop::query();
        if (method_exists(\App\Models\BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    public function mount(NotificationChannel $notificationChannel) { $this->item = $notificationChannel; $this->fill($notificationChannel->toArray());  }
    public function render() { abort_if_cannot('edit_notification_channels'); return view('livewire.admin.notification-channels.edit', [
            'barberShops' => $this->getbarberShopsList(),
        ])->layout('components.layouts.app'); }
    public function update(UpdateNotificationChannelAction $action) { $this->validate();  $dto = NotificationChannelDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'channel' => $this->channel,
            'enabled' => $this->enabled,
            'daily_limit' => $this->daily_limit,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('notification-channels.updated')); return to_route('admin.notification-channels.index'); }
    protected function rules(): array { $rules = NotificationChannel::rules($this->item->id);
        return $rules; }
}