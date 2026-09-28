<?php

namespace App\Livewire\Admin\Barbers;

use App\Models\Barber;
use App\Domain\Barber\DTOs\BarberDTO;
use App\Domain\Barber\Actions\UpdateBarberAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

#[Title('Edit Barber')]
class Edit extends Component
{
        use WithPagination, WithFileUploads;
 public Barber $item;
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

    public function mount(Barber $barber) { $this->item = $barber; $this->fill($barber->toArray());  }
    public function render() { abort_if_cannot('edit_barbers'); return view('livewire.admin.barbers.edit', [
            'barberShops' => $this->getbarberShopsList(),
            'users' => $this->getusersList(),
        ])->layout('components.layouts.app'); }
    public function update(UpdateBarberAction $action) { $this->validate();         if ($this->photo && !is_string($this->photo)) { $this->photo = app(\App\Services\ImageUploadService::class)->upload($this->photo, 'uploads/barbers'); }
 $dto = BarberDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'photo' => $this->photo,
            'bio' => $this->bio,
            'active' => $this->active,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('barbers.updated')); return to_route('admin.barbers.index'); }
    protected function rules(): array { $rules = Barber::rules($this->item->id);
        if ($this->photo instanceof \Illuminate\Http\UploadedFile) { $rules['photo'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->photo) && $this->photo !== '') { $rules['photo'] = ['nullable', 'string', 'max:255']; } else { $rules['photo'] = ['nullable']; }
        return $rules; }
}