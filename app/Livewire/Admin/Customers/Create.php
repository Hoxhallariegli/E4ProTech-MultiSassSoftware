<?php

namespace App\Livewire\Admin\Customers;

use App\Models\Customer;
use App\Domain\Customer\DTOs\CustomerDTO;
use App\Domain\Customer\Actions\CreateCustomerAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

#[Title('Add Customer')]
class Create extends Component
{
        use WithPagination, WithFileUploads;
     public $barber_shop_id = '';
    public $name = '';
    public $phone = '';
    public $email = '';
    public $photo = '';
    public $total_bookings = '';
    public $no_show_count = '';
    public $blocked_at = '';
 
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
 
    public function mount() { if (auth()->check() && auth()->user()->barber_shop_id) { $this->barber_shop_id = auth()->user()->barber_shop_id; } }

    public function render() { abort_if_cannot('add_customers'); return view('livewire.admin.customers.create', [
            'barberShops' => $this->getbarberShopsList(),
        ])->layout('components.layouts.app'); }
    public function store(CreateCustomerAction $action) { $this->validate();         if ($this->photo && !is_string($this->photo)) { $this->photo = app(\App\Services\ImageUploadService::class)->upload($this->photo, 'uploads/customers'); }
 $dto = CustomerDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'photo' => $this->photo,
            'total_bookings' => $this->total_bookings,
            'no_show_count' => $this->no_show_count,
            'blocked_at' => $this->blocked_at,
        ]); $action->execute($dto); session()->flash('success', __('customers.created')); return to_route('admin.customers.index'); }
    protected function rules(): array { $rules = Customer::rules();
        if ($this->photo instanceof \Illuminate\Http\UploadedFile) { $rules['photo'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->photo) && $this->photo !== '') { $rules['photo'] = ['nullable', 'string', 'max:255']; } else { $rules['photo'] = ['nullable']; }
        return $rules; }
}