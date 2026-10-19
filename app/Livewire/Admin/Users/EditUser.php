<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit User')]
class EditUser extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user;

        if ($this->user->id !== auth()->id()) {
            abort_if_cannot('edit_users');

            if (!auth()->user()?->is_global_admin) {
                $isShopMember = User::forActiveShop()->where('users.id', $user->id)->exists();
                if (!$isShopMember) {
                    abort(403, 'Ju nuk keni akses për të ndryshuar këtë përdorues.');
                }
            }
        }
    }

    public function render(): View
    {
        return view('livewire.admin.users.edit');
    }
}
