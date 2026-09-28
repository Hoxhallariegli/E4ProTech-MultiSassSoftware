<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Users\Edit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Roles extends Component
{
    public User $user;

    /**
     * @var Collection<int, Role>
     */
    public Collection $roles;

    /**
     * @var array<int>
     */
    public array $roleSelections = [];

    public function mount(): void
    {
        // Fetch roles for the active team PLUS global roles
        $activeShopId = auth()->user()->barber_shop_id ?: 0;
        $query = Role::whereIn('barber_shop_id', [$activeShopId, 0])
            ->orWhereNull('barber_shop_id');

        // SECURITY: If the logged-in user is NOT a global admin, hide the 'admin' role
        if (!auth()->user()->is_global_admin) {
            $query->where('name', '!=', 'admin');
        }

        $this->roles = $query->orderby('name')->get();
        $this->roleSelections = $this->user->roles->pluck('id')->toArray();
    }

    public function render(): View
    {
        return view('livewire.admin.users.edit.roles');
    }

    public function update(): bool
    {
        // 1. Get the admin role from Global Context (Team 0) to avoid "RoleDoesNotExist"
        $originalTeamId = getPermissionsTeamId();
        setPermissionsTeamId(0);
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        setPermissionsTeamId($originalTeamId);

        // 2. Check if we are trying to remove the last global admin
        if (!in_array($adminRole->id, $this->roleSelections)) {
            // Check count in Team 0
            setPermissionsTeamId(0);
            $adminRolesCount = User::role('admin')->count();
            setPermissionsTeamId($originalTeamId);

            if ($adminRolesCount === 1 && $this->user->is_global_admin) {
                flash('There must be at least one global admin user!')->error();
                return false;
            }
        }

        $this->syncRoles();

        return true;
    }

    protected function syncRoles(): void
    {
        // We sync roles for the CURRENT active team context
        $activeShopId = auth()->user()->barber_shop_id ?: 0;

        $originalTeamId = getPermissionsTeamId();
        setPermissionsTeamId($activeShopId);

        $this->user->syncRoles($this->roleSelections);

        setPermissionsTeamId($originalTeamId);

        add_user_log([
            'title' => 'updated '.$this->user->name."'s roles",
            'reference_id' => $this->user->id,
            'link' => route('admin.users.edit', ['user' => $this->user->id]),
            'section' => 'Users',
            'type' => 'Update',
        ]);

        flash('Roles Updated!')->success();
    }
}
