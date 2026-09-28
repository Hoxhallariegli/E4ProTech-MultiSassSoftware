<?php

namespace App\Livewire\Admin\EventSettings;

use App\Models\EventSetting;
use App\Domain\EventSetting\DTOs\EventSettingDTO;
use App\Domain\EventSetting\Actions\CreateEventSettingAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class QuickCreate extends Component
{
        use WithPagination;
     public $barber_shop_id = '';
    public $realtime_event_id = '';
    public $reverb_enabled = false;
    public $firebase_enabled = false;
 
    #[On('barber-shop-created')] 
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }

    #[On('realtime-event-created')] 
    public function refreshRealtimeEvents($id) { $this->realtime_event_id = $id; $this->updatedRealtimeEventId($id); }
 
    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
        if (isset($related->realtime_event_id)) { $this->realtime_event_id = $related->realtime_event_id; }
    }

    public function updatedRealtimeEventId($value)
    {
        if (!$value) return;
        $related = \App\Models\RealtimeEvent::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id)) { $this->barber_shop_id = $related->barber_shop_id; }
    }
 
    protected function getbarberShopsList() {
        $query = \App\Models\BarberShop::query();
        if (method_exists(\App\Models\BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    protected function getrealtimeEventsList() {
        $query = \App\Models\RealtimeEvent::query();
        if (method_exists(\App\Models\RealtimeEvent::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('event', 'id')->toArray();
    }

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.event-settings.quick-create', [
            'barberShops' => $this->getbarberShopsList(),
            'realtimeEvents' => $this->getrealtimeEventsList(),
        ]); }

    public function store(CreateEventSettingAction $action)
    {
        $this->validate();
        $dto = EventSettingDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'realtime_event_id' => $this->realtime_event_id,
            'reverb_enabled' => $this->reverb_enabled,
            'firebase_enabled' => $this->firebase_enabled,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('event-setting-created', id: $item->id);
        $this->js("Livewire.dispatch('event-setting-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('event-settings.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['barber_shop_id', 'realtime_event_id', 'reverb_enabled', 'firebase_enabled']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = EventSetting::rules();
        return $rules; }
}