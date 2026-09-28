<?php

namespace App\Livewire\Admin\NotificationChannels;

use App\Models\NotificationChannel;
use App\Domain\NotificationChannel\DTOs\NotificationChannelDTO;
use App\Domain\NotificationChannel\Actions\CreateNotificationChannelAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Add NotificationChannel')]
class Create extends Component
{
        use WithPagination;
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
 
    public function mount() { if (auth()->check() && auth()->user()->barber_shop_id) { $this->barber_shop_id = auth()->user()->barber_shop_id; } }

    public function render() { abort_if_cannot('add_notification_channels'); return view('livewire.admin.notification-channels.create', [
            'barberShops' => $this->getbarberShopsList(),
        ])->layout('components.layouts.app'); }
    public function store(CreateNotificationChannelAction $action) { $this->validate();  $dto = NotificationChannelDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'channel' => $this->channel,
            'enabled' => $this->enabled,
            'daily_limit' => $this->daily_limit,
        ]); $action->execute($dto); session()->flash('success', __('notification-channels.created')); return to_route('admin.notification-channels.index'); }
    protected function rules(): array { $rules = NotificationChannel::rules();
        return $rules; }
}