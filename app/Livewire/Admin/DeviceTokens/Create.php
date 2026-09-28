<?php

namespace App\Livewire\Admin\DeviceTokens;

use App\Models\DeviceToken;
use App\Domain\DeviceToken\DTOs\DeviceTokenDTO;
use App\Domain\DeviceToken\Actions\CreateDeviceTokenAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Add DeviceToken')]
class Create extends Component
{
        use WithPagination;
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
 
    public function mount() { if (auth()->check() && auth()->user()->barber_shop_id) { $this->barber_shop_id = auth()->user()->barber_shop_id; } }

    public function render() { abort_if_cannot('add_device_tokens'); return view('livewire.admin.device-tokens.create', [
            'barberShops' => $this->getbarberShopsList(),
            'users' => $this->getusersList(),
        ])->layout('components.layouts.app'); }
    public function store(CreateDeviceTokenAction $action) { $this->validate();  $dto = DeviceTokenDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'user_id' => $this->user_id,
            'fcm_token' => $this->fcm_token,
            'platform' => $this->platform,
            'last_used_at' => $this->last_used_at,
        ]); $action->execute($dto); session()->flash('success', __('device-tokens.created')); return to_route('admin.device-tokens.index'); }
    protected function rules(): array { $rules = DeviceToken::rules();
        return $rules; }
}