<?php

namespace App\Livewire\Admin\DeviceTokens;

use App\Models\DeviceToken;
use App\Domain\DeviceToken\Queries\DeviceTokenListQuery;
use App\Domain\DeviceToken\Actions\DeleteDeviceTokenAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('DeviceTokens')]
class DeviceTokens extends Component
{
    use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $barber_shop_id = '';
    #[Url(history: true)] public $user_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'barber_shop_id', 'user_id', ]); $this->resetPage(); }

    #[On('echo-private:mobile.device-tokens,.device-tokens.changed')]
    public function onBroadcastCloudChange($event)
    {
        $this->render();
    }

    public function toggleGateway($id)
    {
        abort_if_cannot('edit_device_tokens');
        $item = DeviceToken::find($id);
        if (!$item) return;

        $newGatewayState = !$item->is_sms_gateway;

        if ($newGatewayState && $item->barber_shop_id) {
            DeviceToken::where('barber_shop_id', $item->barber_shop_id)
                ->where('id', '!=', $item->id)
                ->update(['is_sms_gateway' => false]);
        }

        $item->update(['is_sms_gateway' => $newGatewayState]);

        if ($item->barber_shop_id && $newGatewayState) {
            \App\Models\BarberShop::where('id', $item->barber_shop_id)->update(['sms_enabled' => true]);
        }

        $this->dispatch('toast', message: $newGatewayState ? 'Pajisja u caktua si SMS Gateway me sukses!' : 'Pajisja u hoq nga SMS Gateway.', type: 'success');
    }

    public function render()
    {
        abort_if_cannot('view_device_tokens');
        $query = (new DeviceTokenListQuery())->handle([
            'search' => $this->search,
            'barber_shop_id' => $this->barber_shop_id,
            'user_id' => $this->user_id,
        ], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.device-tokens.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => DeviceToken::sortable(),
            'barberShops' => \App\Models\BarberShop::pluck('name', 'id')->toArray(),
            'users' => \App\Models\User::pluck('name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, DeviceToken::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteDeviceToken($id, DeleteDeviceTokenAction $action)
    {
        abort_if_cannot('delete_device_tokens');
        $item = DeviceToken::find($id);
        if (!$item) { $this->dispatch('toast', message: __('device-tokens.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('device-tokens.deleted'), type: 'success'); $this->resetPage(); }
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('device-tokens.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('device-tokens.delete_error'), type: 'error'); }
    }
}
