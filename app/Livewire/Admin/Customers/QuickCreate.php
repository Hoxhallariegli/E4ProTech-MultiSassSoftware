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

class QuickCreate extends Component
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

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.customers.quick-create', [
            'barberShops' => $this->getbarberShopsList(),
        ]); }

    public function store(CreateCustomerAction $action)
    {
        $this->validate();
        if ($this->photo && !is_string($this->photo)) { $this->photo = app(\App\Services\ImageUploadService::class)->upload($this->photo, 'uploads/customers'); }
        $dto = CustomerDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'photo' => $this->photo,
            'total_bookings' => $this->total_bookings,
            'no_show_count' => $this->no_show_count,
            'blocked_at' => $this->blocked_at,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('customer-created', id: $item->id);
        $this->js("Livewire.dispatch('customer-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('customers.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->name ?? $item->id);
        $this->reset(['barber_shop_id', 'name', 'phone', 'email', 'photo', 'total_bookings', 'no_show_count', 'blocked_at']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = Customer::rules();
        if ($this->photo instanceof \Illuminate\Http\UploadedFile) { $rules['photo'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->photo) && $this->photo !== '') { $rules['photo'] = ['nullable', 'string', 'max:255']; } else { $rules['photo'] = ['nullable']; }
        return $rules; }
}