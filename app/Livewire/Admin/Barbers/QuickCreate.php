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

class QuickCreate extends Component
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

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.barbers.quick-create', [
            'barberShops' => $this->getbarberShopsList(),
            'users' => $this->getusersList(),
        ]); }

    public function store(CreateBarberAction $action)
    {
        $this->validate();
        if ($this->photo && !is_string($this->photo)) { $this->photo = app(\App\Services\ImageUploadService::class)->upload($this->photo, 'uploads/barbers'); }
        $dto = BarberDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'photo' => $this->photo,
            'bio' => $this->bio,
            'active' => $this->active,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('barber-created', id: $item->id);
        $this->js("Livewire.dispatch('barber-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('barbers.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->name ?? $item->id);
        $this->reset(['barber_shop_id', 'user_id', 'name', 'phone', 'photo', 'bio', 'active']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = Barber::rules();
        if ($this->photo instanceof \Illuminate\Http\UploadedFile) { $rules['photo'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->photo) && $this->photo !== '') { $rules['photo'] = ['nullable', 'string', 'max:255']; } else { $rules['photo'] = ['nullable']; }
        return $rules; }
}