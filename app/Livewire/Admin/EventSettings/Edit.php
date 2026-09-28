<?php

namespace App\Livewire\Admin\EventSettings;

use App\Models\EventSetting;
use App\Domain\EventSetting\DTOs\EventSettingDTO;
use App\Domain\EventSetting\Actions\UpdateEventSettingAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit EventSetting')]
class Edit extends Component
{
        use WithPagination;
 public EventSetting $item;
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

    public function mount(EventSetting $eventSetting) { $this->item = $eventSetting; $this->fill($eventSetting->toArray());  }
    public function render() { abort_if_cannot('edit_event_settings'); return view('livewire.admin.event-settings.edit', [
            'barberShops' => $this->getbarberShopsList(),
            'realtimeEvents' => $this->getrealtimeEventsList(),
        ])->layout('components.layouts.app'); }
    public function update(UpdateEventSettingAction $action) { $this->validate();  $dto = EventSettingDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'realtime_event_id' => $this->realtime_event_id,
            'reverb_enabled' => $this->reverb_enabled,
            'firebase_enabled' => $this->firebase_enabled,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('event-settings.updated')); return to_route('admin.event-settings.index'); }
    protected function rules(): array { $rules = EventSetting::rules($this->item->id);
        return $rules; }
}