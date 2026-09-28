<?php

namespace App\Livewire\Admin\DeviceTokens;

use App\Models\DeviceToken;
use App\Domain\DeviceToken\DTOs\DeviceTokenDTO;
use App\Domain\DeviceToken\Actions\UpdateDeviceTokenAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit DeviceToken')]
class Edit extends Component
{
        use WithPagination;
 public DeviceToken $item;
    public $barber_shop_id = '';
    public $user_id = '';
    public $fcm_token = '';
    public $platform = '';
    public $last_used_at = '';
 
    #[On('barber-shop-created')] 
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }

    #[On('user-created')] 
    public function refreshUsers($id) { $this->user_id = $id; $this->updatedUserId($id); }
 
    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
        if (isset($related->user_id)) { $this->user_id = $related->user_id; }
    }

    public function updatedUserId($value)
    {
        if (!$value) return;
        $related = \App\Models\User::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id)) { $this->barber_shop_id = $related->barber_shop_id; }
    }
 
    protected function getbarberShopsList() {
        $query = \App\Models\BarberShop::query();
        if (method_exists(\App\Models\BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    protected function getusersList() {
        $query = \App\Models\User::query();
        if (method_exists(\App\Models\User::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    public function mount(DeviceToken $deviceToken) { $this->item = $deviceToken; $this->fill($deviceToken->toArray()); $this->last_used_at = $deviceToken->last_used_at?->format('Y-m-d\TH:i'); }
    public function render() { abort_if_cannot('edit_device_tokens'); return view('livewire.admin.device-tokens.edit', [
            'barberShops' => $this->getbarberShopsList(),
            'users' => $this->getusersList(),
        ])->layout('components.layouts.app'); }
    public function update(UpdateDeviceTokenAction $action) { $this->validate();  $dto = DeviceTokenDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'user_id' => $this->user_id,
            'fcm_token' => $this->fcm_token,
            'platform' => $this->platform,
            'last_used_at' => $this->last_used_at,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('device-tokens.updated')); return to_route('admin.device-tokens.index'); }
    protected function rules(): array { $rules = DeviceToken::rules($this->item->id);
        return $rules; }
}