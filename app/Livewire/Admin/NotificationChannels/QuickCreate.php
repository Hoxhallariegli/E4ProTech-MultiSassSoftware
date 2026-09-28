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

class QuickCreate extends Component
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

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.notification-channels.quick-create', [
            'barberShops' => $this->getbarberShopsList(),
        ]); }

    public function store(CreateNotificationChannelAction $action)
    {
        $this->validate();
        $dto = NotificationChannelDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'channel' => $this->channel,
            'enabled' => $this->enabled,
            'daily_limit' => $this->daily_limit,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('notification-channel-created', id: $item->id);
        $this->js("Livewire.dispatch('notification-channel-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('notification-channels.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['barber_shop_id', 'channel', 'enabled', 'daily_limit']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = NotificationChannel::rules();
        return $rules; }
}