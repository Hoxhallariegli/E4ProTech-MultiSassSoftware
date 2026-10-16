<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Users;

use App\Actions\GetInitialsAction;
use App\Mail\Users\SendInviteMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Invite extends Component
{
    use WithPagination;

    public string $name = '';

    public string $email = '';

    /**
     * @var array<int>
     */
    public array $rolesSelected = [];

    /**
     * @var array<string, array<int, string>>
     */
    protected array $rules = [
        'name' => [
            'required',
            'string',
        ],
        'email' => [
            'required',
            'string',
            'email',
            'unique:users,email',
        ],
        'rolesSelected' => [
            'required',
            'min:1',
        ],
    ];

    /**
     * @var array<string, string>
     */
    protected array $messages = [
        'name.required' => 'Name is required',
        'email.required' => 'Email is required',
        'rolesSelected.required' => 'A role is required',
    ];

    /**
     * @throws ValidationException
     */
    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function render(): View
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

        $roles = $query->orderby('name')->get();

        return view('livewire.admin.users.invite', compact('roles'));
    }

    public function store(GetInitialsAction $getInitialsAction): void
    {
        $this->validate();

        $currentUser = auth()->user();

        $user = User::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'email' => $this->email,
            'is_active' => 0,
            'is_office_login_only' => 0,
            'invite_token' => Str::random(32),
            'invited_by' => auth()->id(),
            'invited_at' => now(),
            'barber_shop_id' => $currentUser?->barber_shop_id,
        ]);

        // generate image
        $name = $getInitialsAction($user->name);
        $id = $user->id.'.png';
        $path = 'users/';
        $imagePath = create_avatar($name, $id, $path);

        // save image
        $user->image = $imagePath;
        $user->save();

        foreach ($this->rolesSelected as $roleId) {
            $role = is_numeric($roleId) ? Role::find($roleId) : Role::where('name', $roleId)->first();
            if (!$role) continue;

            // Security guard: Non-global-admins cannot assign the 'admin' role
            if ($role->name === 'admin' && !$currentUser?->is_global_admin) {
                continue;
            }

            $user->assignRole($role);
        }

        try {
            Mail::send(new SendInviteMail($user));
        } catch (\Exception $e) {
            // Log mail failure
        }

        add_user_log([
            'title' => 'invited '.$user->name,
            'reference_id' => $user->id,
            'section' => 'Auth',
            'type' => 'Join',
        ]);

        flash('User invited')->success();

        $this->dispatch('refreshUsers');
        $this->dispatch('close-modal');
    }

    public function cancel(): void
    {
        $this->reset();
        $this->dispatch('close-modal');
    }
}
