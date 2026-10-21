<?php

namespace App\Livewire\Admin\EventSettings;

use App\Models\EventSetting;
use App\Models\RealtimeEvent;
use App\Models\BarberShop;
use App\Domain\EventSetting\Queries\EventSettingListQuery;
use App\Domain\EventSetting\Actions\DeleteEventSettingAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('EventSettings')]
class EventSettings extends Component
{
    use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $barber_shop_id = '';
    #[Url(history: true)] public $realtime_event_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = false;

    public function mount()
    {
        $this->ensureEventSettingsForShops();
    }

    public function ensureEventSettingsForShops(): void
    {
        if (!auth()->check()) return;

        $user = auth()->user();
        $shopIds = [];

        if ($user->is_global_admin) {
            $shopIds = BarberShop::pluck('id')->toArray();
        } else {
            if ($user->barber_shop_id) $shopIds[] = $user->barber_shop_id;
            $roleShops = \Illuminate\Support\Facades\DB::table('model_has_roles')
                ->where('model_id', $user->id)
                ->where('barber_shop_id', '>', 0)
                ->pluck('barber_shop_id')
                ->toArray();
            $shopIds = array_unique(array_merge($shopIds, $roleShops));
        }

        if (empty($shopIds)) return;

        $realtimeEvents = RealtimeEvent::all();
        if ($realtimeEvents->isEmpty() && class_exists(\Database\Seeders\RealtimeEventsSeeder::class)) {
            app(\Database\Seeders\RealtimeEventsSeeder::class)->run();
            $realtimeEvents = RealtimeEvent::all();
        }

        foreach ($shopIds as $shopId) {
            foreach ($realtimeEvents as $event) {
                EventSetting::firstOrCreate(
                    [
                        'barber_shop_id' => $shopId,
                        'realtime_event_id' => $event->id,
                    ],
                    [
                        'reverb_enabled' => false,
                        'firebase_enabled' => (bool) $event->firebase_enabled,
                    ]
                );
            }
        }
    }

    public function resetFilters() { $this->reset(['search', 'openFilter', 'barber_shop_id', 'realtime_event_id']); $this->resetPage(); }

    #[On('echo-private:mobile.event-settings,.event-settings.changed')]
    public function onBroadcastCloudChange($event)
    {
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_event_settings');
        $query = (new EventSettingListQuery())->handle([
            'search' => $this->search,
            'barber_shop_id' => $this->barber_shop_id,
            'realtime_event_id' => $this->realtime_event_id,
        ], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.event-settings.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => EventSetting::sortable(),
            'barberShops' => BarberShop::pluck('name', 'id')->toArray(),
            'realtimeEvents' => RealtimeEvent::pluck('event', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, EventSetting::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteEventSetting($id, DeleteEventSettingAction $action)
    {
        abort_if_cannot('delete_event_settings');
        $item = EventSetting::find($id);
        if (!$item) { $this->dispatch('toast', message: __('event-settings.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('event-settings.deleted'), type: 'success'); $this->resetPage(); }
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('event-settings.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('event-settings.delete_error'), type: 'error'); }
    }
}
