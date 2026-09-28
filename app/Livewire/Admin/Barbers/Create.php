<?php

namespace App\Livewire\Admin\Barbers;

use App\Models\Barber;
use App\Domain\Barber\DTOs\BarberDTO;
use App\Domain\Barber\Actions\CreateBarberAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

#[Title('Add Barber')]
class Create extends Component
{
        use WithPagination, WithFileUploads;
     public $barber_shop_id = '';
    public $user_id = '';
    public $name = '';
    public $phone = '';
    public $photo = '';
    public $bio = '';
    public $active = false;
 
    #[On('barber-shop-created')] 
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }

    #[On('user-created')] 
    public function refreshUsers($id) { $this->user_id = $id; $this->updatedUserId($id); }
 
    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
        if (isset($related->user_id)) { $this->user_id = $related->user_id; }
    }

    public function updatedUserId($value)
    {
        if (!$value) return;
        $related = \App\Models\User::find($value);
        if (!$related) return;
        if (isset($related->barber_shop_id)) { $this->barber_shop_id = $related->barber_shop_id; }
    }
 
    protected function getbarberShopsList() {
        $query = \App\Models\BarberShop::query();
        if (method_exists(\App\Models\BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    protected function getusersList() {
        $query = \App\Models\User::query();
        if (method_exists(\App\Models\User::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }
 
    public function mount() { if (auth()->check() && auth()->user()->barber_shop_id) { $this->barber_shop_id = auth()->user()->barber_shop_id; } }

    public function render() { abort_if_cannot('add_barbers');         $shop = \App\Models\BarberShop::find($this->barber_shop_id);
        $canAdd = true;
        if ($shop && !auth()->user()->hasRole(['admin', 'qqq'])) {
            $canAdd = app(\App\Services\SubscriptionService::class)->canAddBarber($shop);
        }
        return view('livewire.admin.barbers.create', [
            'limitReached' => !$canAdd,
            'barberShops' => $this->getbarberShopsList(),
            'users' => $this->getusersList(),
        ])->layout('components.layouts.app'); }
    public function store(CreateBarberAction $action) { $this->validate();         if ($this->photo && !is_string($this->photo)) { $this->photo = app(\App\Services\ImageUploadService::class)->upload($this->photo, 'uploads/barbers'); }
 $dto = BarberDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'photo' => $this->photo,
            'bio' => $this->bio,
            'active' => $this->active,
        ]);         $shop = \App\Models\BarberShop::find($this->barber_shop_id);
        if ($shop && !auth()->user()->hasRole(['admin', 'qqq'])) {
            if (!app(\App\Services\SubscriptionService::class)->canAddBarber($shop)) {
                session()->flash('error', __('Limit reached for this plan.'));
                return;
            }
        }
        $action->execute($dto); session()->flash('success', __('barbers.created')); return to_route('admin.barbers.index'); }
    protected function rules(): array { $rules = Barber::rules();
        if ($this->photo instanceof \Illuminate\Http\UploadedFile) { $rules['photo'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->photo) && $this->photo !== '') { $rules['photo'] = ['nullable', 'string', 'max:255']; } else { $rules['photo'] = ['nullable']; }
        return $rules; }
}