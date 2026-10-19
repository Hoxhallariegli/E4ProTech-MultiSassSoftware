<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Models\Role;
use App\Models\Barber;
use App\Actions\GetInitialsAction;
use App\Mail\Users\SendInviteMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;

class QuickCreate extends Component
{
    public $name = '';
    public $email = '';
    public array $rolesSelected = [];

    public bool $created = false;
    public ?string $createdId = null;
    public string $createdLabel = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'rolesSelected' => ['required', 'array', 'min:1'],
        ];
    }

    public function render()
    {
        $currentUser = auth()->user();
        $activeShopId = $currentUser?->barber_shop_id ?: 0;

        $query = Role::query();

        if ($activeShopId && !$currentUser?->is_global_admin) {
            $query->where(function ($q) use ($activeShopId) {
                $q->where('barber_shop_id', $activeShopId)
                  ->orWhereNull('barber_shop_id')
                  ->orWhere('barber_shop_id', 0);
            });
        }

        // SECURITY: If the logged-in user is NOT a global admin, hide the 'admin' role
        if (!$currentUser?->is_global_admin) {
            $query->where('name', '!=', 'admin');
        }

        $roles = $query->orderBy('name')->get();

        return view('livewire.admin.users.quick-create', compact('roles'));
    }

    public function store(GetInitialsAction $getInitialsAction)
    {
        $this->validate();

        $currentUser = auth()->user();
        $activeShopId = $currentUser?->barber_shop_id ?: 0;

        $user = User::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'email' => $this->email,
            'is_active' => 0,
            'is_office_login_only' => 0,
            'invite_token' => Str::random(32),
            'invited_by' => auth()->id(),
            'invited_at' => now(),
            'barber_shop_id' => $activeShopId ?: null,
        ]);

        // Link user to barber_shop_user pivot table
        if ($user->barber_shop_id) {
            $user->barberShops()->syncWithoutDetaching([$user->barber_shop_id]);
        }

        // generate image
        $initials = $getInitialsAction($user->name);
        $filename = $user->id.'.png';
        $path = 'users/';
        $imagePath = create_avatar($initials, $filename, $path);

        // save image
        $user->image = $imagePath;
        $user->save();

        $originalTeamId = getPermissionsTeamId();
        setPermissionsTeamId($activeShopId);

        $hasBarberRole = false;

        try {
            foreach ($this->rolesSelected as $roleId) {
                $role = Role::find($roleId) ?? Role::where('name', $roleId)->first();
                if (!$role) continue;

                // Security guard: Non-global-admins cannot assign the 'admin' role
                if ($role->name === 'admin' && !$currentUser?->is_global_admin) {
                    continue;
                }

                try {
                    $user->assignRole($role);
                } catch (\Throwable $e) {}

                // Direct DB insertion into model_has_roles to guarantee role attachment
                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id' => $role->id,
                    'model_type' => 'App\Models\User',
                    'model_id' => $user->id,
                    'barber_shop_id' => $activeShopId ?: 0,
                ]);

                if (in_array(strtolower($role->name), ['berber', 'barber', 'staff', 'staf', 'parukier', 'estetist', 'pronar_i_biznesit_/_sallonit'])) {
                    $hasBarberRole = true;
                }
            }
        } finally {
            setPermissionsTeamId($originalTeamId);
        }

        // Auto-create Barber staff record if assigned a barber role
        if ($hasBarberRole && $user->barber_shop_id) {
            Barber::firstOrCreate(
                ['user_id' => $user->id, 'barber_shop_id' => $user->barber_shop_id],
                [
                    'name' => $user->name,
                    'phone' => '+355690000000',
                    'active' => true,
                ]
            );
        }

        try {
            Mail::send(new SendInviteMail($user));
        } catch (\Exception $e) {
            // Log mail failure but don't stop the process
        }

        add_user_log([
            'title' => 'invited '.$user->name,
            'reference_id' => $user->id,
            'section' => 'Auth',
            'type' => 'Join',
        ]);

        $this->dispatch('user-created', id: $user->id);
        $this->js("Livewire.dispatch('user-created', { id: '{$user->id}' })");
        $this->dispatch('toast', message: __('users.created'), type: 'success');

        $this->created = true;
        $this->createdId = $user->id;
        $this->createdLabel = $user->name;

        $this->reset(['name', 'email', 'rolesSelected']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }
}
