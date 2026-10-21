<?php

namespace App\Livewire\Admin\Customers;

use App\Models\Customer;
use App\Domain\Customer\DTOs\CustomerDTO;
use App\Domain\Customer\Actions\UpdateCustomerAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

#[Title('Edit Customer')]
class Edit extends Component
{
    use WithPagination, WithFileUploads;

    public Customer $item;
    public $barber_shop_id = '';
    public $name = '';
    public $phone = '';
    public $email = '';
    public $photo = '';
    public bool $is_blocked = false;

    #[On('barber-shop-created')]
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }

    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
    }

    protected function getbarberShopsList() {
        $query = \App\Models\BarberShop::query();
        if (method_exists(\App\Models\BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    public function mount(Customer $customer) {
        $this->item = $customer;
        $this->fill($customer->only(['barber_shop_id', 'name', 'phone', 'email', 'photo']));
        $this->is_blocked = !empty($customer->blocked_at);
    }

    public function render() { abort_if_cannot('edit_customers'); return view('livewire.admin.customers.edit', [
            'barberShops' => $this->getbarberShopsList(),
        ])->layout('components.layouts.app'); }

    public function update(UpdateCustomerAction $action) {
        if (auth()->check() && auth()->user()->barber_shop_id) { $this->barber_shop_id = auth()->user()->barber_shop_id; }
        $this->validate();
        if ($this->photo && !is_string($this->photo)) { $this->photo = app(\App\Services\ImageUploadService::class)->upload($this->photo, 'uploads/customers'); }

        $blockedAt = $this->is_blocked
            ? ($this->item->blocked_at ? $this->item->blocked_at->toDateTimeString() : now()->toDateTimeString())
            : null;

        $dto = CustomerDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'photo' => $this->photo,
            'blocked_at' => $blockedAt,
        ]);
        $action->execute($this->item, $dto);
        session()->flash('success', __('customers.updated'));
        return to_route('admin.customers.index');
    }

    protected function rules(): array {
        $rules = [
            'barber_shop_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'is_blocked' => ['nullable', 'boolean'],
        ];
        if ($this->photo instanceof \Illuminate\Http\UploadedFile) { $rules['photo'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->photo) && $this->photo !== '') { $rules['photo'] = ['nullable', 'string', 'max:255']; } else { $rules['photo'] = ['nullable']; }
        return $rules;
    }
}
