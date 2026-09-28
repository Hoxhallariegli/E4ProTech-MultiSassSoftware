<?php

namespace App\Livewire\Admin\BarberShops;

use App\Models\BarberShop;
use App\Domain\BarberShop\DTOs\BarberShopDTO;
use App\Domain\BarberShop\Actions\CreateBarberShopAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

class QuickCreate extends Component
{
        use WithPagination, WithFileUploads;
     public $owner_id = '';
    public $name = '';
    public $app_name = '';
    public $slug = '';
    public $logo = '';
    public $banner = '';
    public $primary_color = '';
    public $secondary_color = '';
    public $trial_ends_at = '';
    public $expires_at = '';
    public $active = false;
    public $sms_enabled = false;
    public $timezone = '';
    public $max_no_show_before_block = '';
 
    #[On('user-created')] 
    public function refreshOwners($id) { $this->owner_id = $id; $this->updatedOwnerId($id); }
 
    public function updatedOwnerId($value)
    {
        if (!$value) return;
        $related = \App\Models\User::find($value);
        if (!$related) return;
    }
 
    protected function getownersList() {
        $query = \App\Models\User::query();
        if (method_exists(\App\Models\User::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.barber-shops.quick-create', [
            'owners' => $this->getownersList(),
        ]); }

    public function store(CreateBarberShopAction $action)
    {
        $this->validate();
        if ($this->logo && !is_string($this->logo)) { $this->logo = app(\App\Services\ImageUploadService::class)->upload($this->logo, 'uploads/barber-shops'); }
        if ($this->banner && !is_string($this->banner)) { $this->banner = app(\App\Services\ImageUploadService::class)->upload($this->banner, 'uploads/barber-shops'); }
        $dto = BarberShopDTO::fromArray([
            'owner_id' => $this->owner_id,
            'name' => $this->name,
            'app_name' => $this->app_name,
            'slug' => $this->slug,
            'logo' => $this->logo,
            'banner' => $this->banner,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'trial_ends_at' => $this->trial_ends_at,
            'expires_at' => $this->expires_at,
            'active' => $this->active,
            'sms_enabled' => $this->sms_enabled,
            'timezone' => $this->timezone,
            'max_no_show_before_block' => $this->max_no_show_before_block,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('barber-shop-created', id: $item->id);
        $this->js("Livewire.dispatch('barber-shop-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('barber-shops.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->name ?? $item->id);
        $this->reset(['owner_id', 'name', 'app_name', 'slug', 'logo', 'banner', 'primary_color', 'secondary_color', 'trial_ends_at', 'expires_at', 'active', 'sms_enabled', 'timezone', 'max_no_show_before_block']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = BarberShop::rules();
        if ($this->logo instanceof \Illuminate\Http\UploadedFile) { $rules['logo'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->logo) && $this->logo !== '') { $rules['logo'] = ['nullable', 'string', 'max:255']; } else { $rules['logo'] = ['nullable']; }
        if ($this->banner instanceof \Illuminate\Http\UploadedFile) { $rules['banner'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->banner) && $this->banner !== '') { $rules['banner'] = ['nullable', 'string', 'max:255']; } else { $rules['banner'] = ['nullable']; }
        return $rules; }
}