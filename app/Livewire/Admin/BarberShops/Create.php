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

#[Title('Add BarberShop')]
class Create extends Component
{
    use WithPagination, WithFileUploads;

    public $owner_id = '';
    public $name = '';
    public $app_name = '';
    public $slug = '';
    public $logo = '';
    public $banner = '';
    public $primary_color = '#2563EB';
    public $secondary_color = '#F59E0B';
    public $trial_ends_at = '';
    public $expires_at = '';
    public $active = true;
    public $sms_enabled = true;
    public $timezone = 'Europe/Tirane';
    public $max_no_show_before_block = '3';
    public $min_service_time = '';
    public $business_type = 'barbershop';
    public $staff_label = '';
    public $staff_label_plural = '';
    public $shop_label = '';
    public $service_label = '';
    public $currency = 'Lekë';

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

    public function mount() {
        if (auth()->check() && !auth()->user()->is_global_admin) {
            $this->owner_id = auth()->id();
        }
    }

    public function render() {
        abort_if_cannot('add_barber_shops');
        return view('livewire.admin.barber-shops.create', [
            'owners' => $this->getownersList(),
        ])->layout('components.layouts.app');
    }

    public function store(CreateBarberShopAction $action) {
        if (!auth()->user()->is_global_admin || empty($this->owner_id)) {
            $this->owner_id = auth()->id();
        }
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
            'min_service_time' => $this->min_service_time,
            'business_type' => $this->business_type,
            'staff_label' => $this->staff_label,
            'staff_label_plural' => $this->staff_label_plural,
            'shop_label' => $this->shop_label,
            'service_label' => $this->service_label,
            'currency' => $this->currency,
        ]);
        $action->execute($dto);
        session()->flash('success', __('barber-shops.created'));
        return to_route('admin.barber-shops.index');
    }

    protected function rules(): array {
        $rules = BarberShop::rules();
        if ($this->logo instanceof \Illuminate\Http\UploadedFile) { $rules['logo'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->logo) && $this->logo !== '') { $rules['logo'] = ['nullable', 'string', 'max:255']; } else { $rules['logo'] = ['nullable']; }
        if ($this->banner instanceof \Illuminate\Http\UploadedFile) { $rules['banner'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->banner) && $this->banner !== '') { $rules['banner'] = ['nullable', 'string', 'max:255']; } else { $rules['banner'] = ['nullable']; }
        return $rules;
    }
}
