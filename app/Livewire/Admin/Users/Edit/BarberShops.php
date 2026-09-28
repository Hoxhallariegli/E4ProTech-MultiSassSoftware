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

        // 1. Initialize ALL shops with empty arrays to prevent sharing references
        $allShops = BarberShop::all();
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

        // 2. Clear ONLY shop-specific roles for this user
        // We MUST preserve barber_shop_id = 0 (Global Admin/Templates)
        // so the user doesn't lose global access when editing shops.
        DB::table('model_has_roles')
            ->where('model_id', $this->user->id)
            ->where('barber_shop_id', '>', 0)
            ->delete();

        // 3. Assign new roles per shop
        foreach ($this->shopRoles as $shopId => $roleIds) {
            if (!empty($roleIds) && in_array((int)$shopId, $selectedIds)) {
                foreach ($roleIds as $roleId) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $roleId,
                        'model_id' => $this->user->id,
                        'model_type' => 'App\Models\User',
                        'barber_shop_id' => (int)$shopId
                    ]);
                }
            }
        }

        // 4. Sync pivot table barber_shop_user
        $this->user->barberShops()->sync($selectedIds);

        // 5. Update Primary Shop
        $this->user->update(['barber_shop_id' => $this->primaryShopId]);

        // 6. FORCE Spatie to forget EVERYTHING about this user's permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Clear global cache for safety
        \Illuminate\Support\Facades\Cache::flush();

        $this->dispatch('toast', message: 'Akseset u aktivizuan flakë!', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.users.edit.barber-shops', [
            'allShops' => BarberShop::all(),
            'availableRoles' => Role::where('name', '!=', 'admin')->get() // HIDE ADMIN ROLE
        ]);
    }
}
