<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Roles;

use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component;

class Create extends Component
{
    public bool $showDialog = false;

    public string $label = '';
    public $barber_shop_id = 0; // Default Global

    public function render(): View
    {
        abort_if_cannot('add_roles');

        return view('livewire.admin.roles.create', [
            'allShops' => \App\Models\BarberShop::all()
        ]);
    }

    public function store(): void
    {
        $this->validate();

        // Security: only global admin can choose the shop, others forced to their own
        $finalShopId = auth()->user()->is_global_admin ? $this->barber_shop_id : auth()->user()->barber_shop_id;

        // In roles table, NULL means global template
        $dbShopId = ($finalShopId == 0) ? null : $finalShopId;

        /** @var Role $role */
        $role = Role::create([
            'label' => $this->label,
            'name' => mb_strtolower(str_replace(' ', '_', $this->label)),
            'barber_shop_id' => $dbShopId,
            'guard_name' => 'web'
        ]);

        flash('Role created')->success();

        add_user_log([
            'title' => 'created role '.$this->label,
            'link' => route('admin.settings.roles.edit', ['role' => $role->id]),
            'reference_id' => $role->id,
            'section' => 'Roles',
            'type' => 'created',
        ]);

        $this->reset(['label', 'barber_shop_id']);

        $this->showDialog = false;

        $this->dispatch('added');
    }

    /**
     * @return array<string, array<int, Unique|string>>
     */
    protected function rules(): array
    {
        return [
            'label' => [
                'required',
                'string',
                Rule::unique('roles'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'label.required' => 'The role is required.',
            'label.unique' => 'The role has already been taken.',
        ];
    }
}
