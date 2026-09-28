<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\BarberShop;
use Illuminate\Support\Facades\Auth;

class ShopSwitcher extends Component
{
    public $shops;
    public $activeShopId;

    public function mount()
    {
        $user = Auth::user();

        if ($user->hasRole(['admin', 'qqq'])) {
            $this->shops = BarberShop::all();
        } else {
            // Include both owned shops and shops joined via pivot
            $this->shops = BarberShop::where('owner_id', $user->id)
                ->orWhereHas('users', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                })->get();
        }

        $this->activeShopId = $user->barber_shop_id;
    }

    public function switchShop($shopId)
    {
        $user = Auth::user();

        // Security check: Verify if the user has access to this shop
        $hasAccess = false;
        if ($user->hasRole(['admin', 'qqq'])) {
            $hasAccess = true;
        } else {
            $hasAccess = BarberShop::where('id', $shopId)
                ->where(function($q) use ($user) {
                    $q->where('owner_id', $user->id)
                      ->orWhereHas('users', function($qu) use ($user) {
                          $qu->where('users.id', $user->id);
                      });
                })->exists();
        }

        if ($hasAccess) {
            $user->barber_shop_id = $shopId;
            $user->save();

            // Spatie Teams: Set the team ID for permissions
            setPermissionsTeamId($shopId);

            session()->flash('success', __('Shop switched successfully!'));
            return redirect(request()->header('Referer'));
        }

        session()->flash('error', __('You do not have access to this shop.'));
    }

    public function render()
    {
        return view('livewire.admin.shop-switcher');
    }
}
