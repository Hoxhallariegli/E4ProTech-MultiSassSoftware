<?php

namespace App\Livewire\Admin\Users\Edit;

use App\Models\User;
use App\Models\BarberShop;
use App\Models\Role;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class BarberShops extends Component
{
    public User $user;

    // Structure: [shop_id => [role_id1, role_id2]]
    public $shopRoles = [];
    public $primaryShopId;

    public function mount()
    {
        $this->primaryShopId = $this->user->barber_shop_id;

        $currentUser = auth()->user();
        if ($currentUser?->is_global_admin) {
            $allShops = BarberShop::all();
        } else {
            $allShops = BarberShop::where('id', $currentUser?->barber_shop_id)->get();
        }

        // 1. Initialize shops with empty arrays
        foreach($allShops as $shop) {
            $this->shopRoles[(int)$shop->id] = [];
        }

        // 2. Load existing access from DB
        $access = DB::table('model_has_roles')
            ->where('model_id', $this->user->id)
            ->where('model_type', get_class($this->user))
            ->get();

        foreach ($access as $row) {
            $sid = (int)$row->barber_shop_id;
            if ($sid > 0 && isset($this->shopRoles[$sid])) {
                $this->shopRoles[$sid][] = (string)$row->role_id;
            }
        }
    }

    public function updateAccess()
    {
        $currentUser = auth()->user();

        // 1. Derive selected shop IDs from the shopRoles array keys where values are not empty
        $selectedIds = collect($this->shopRoles)
            ->filter(fn($roles) => !empty($roles))
            ->keys()
            ->map(fn($id) => (int)$id)
            ->toArray();

        // If no shops selected, clear target user's active shop ID
        if (empty($selectedIds)) {
            $this->primaryShopId = null;
        } else {
            // Auto-assign the first selected shop as primary if current primary is no longer valid
            if (!$this->primaryShopId || !in_array((int)$this->primaryShopId, $selectedIds)) {
                $this->primaryShopId = $selectedIds[0];
            }
        }

        // 2. Clear ONLY shop-specific roles for the shops currently being edited
        $editableShopIds = $currentUser?->is_global_admin
            ? BarberShop::pluck('id')->toArray()
            : [$currentUser?->barber_shop_id];

        DB::table('model_has_roles')
            ->where('model_id', $this->user->id)
            ->whereIn('barber_shop_id', $editableShopIds)
            ->delete();

        // 3. Assign new roles per shop
        foreach ($this->shopRoles as $shopId => $roleIds) {
            if (!empty($roleIds) && in_array((int)$shopId, $selectedIds)) {
                foreach ($roleIds as $roleId) {
                    DB::table('model_has_roles')->insertOrIgnore([
                        'role_id' => $roleId,
                        'model_id' => $this->user->id,
                        'model_type' => 'App\Models\User',
                        'barber_shop_id' => (int)$shopId
                    ]);
                }
            }
        }

        // 4. Sync pivot table barber_shop_user for active shop
        if ($currentUser?->is_global_admin) {
            $this->user->barberShops()->sync($selectedIds);
        } else {
            if (!empty($selectedIds)) {
                $this->user->barberShops()->syncWithoutDetaching($selectedIds);
            }
        }

        // 5. Update Primary Shop
        if ($this->primaryShopId) {
            $this->user->update(['barber_shop_id' => $this->primaryShopId]);
        }

        // 6. FORCE Spatie to forget EVERYTHING about this user's permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Clear global cache for safety
        \Illuminate\Support\Facades\Cache::flush();

        $this->dispatch('toast', message: 'Akseset u ruajtën me sukses!', type: 'success');
    }

    public function render()
    {
        $currentUser = auth()->user();
        if ($currentUser?->is_global_admin) {
            $allShops = BarberShop::all();
        } else {
            $allShops = BarberShop::where('id', $currentUser?->barber_shop_id)->get();
        }

        $rolesQuery = Role::query()->where('name', '!=', 'admin');
        if (!$currentUser?->is_global_admin && $currentUser?->barber_shop_id) {
            $shopId = $currentUser->barber_shop_id;
            $rolesQuery->where(function($q) use ($shopId) {
                $q->where('barber_shop_id', $shopId)
                  ->orWhereNull('barber_shop_id')
                  ->orWhere('barber_shop_id', 0);
            });
        }

        return view('livewire.admin.users.edit.barber-shops', [
            'allShops' => $allShops,
            'availableRoles' => $rolesQuery->get()
        ]);
    }
}
