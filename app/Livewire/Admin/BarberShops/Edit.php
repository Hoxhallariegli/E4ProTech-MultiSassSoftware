<?php

namespace App\Livewire\Admin\BarberShops;

use App\Models\BarberShop;
use App\Domain\BarberShop\DTOs\BarberShopDTO;
use App\Domain\BarberShop\Actions\UpdateBarberShopAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

#[Title('Edit BarberShop')]
class Edit extends Component
{
    use WithPagination, WithFileUploads;

    public BarberShop $item;
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
    public $min_service_time = '';
    public $business_type = 'barbershop';
    public $staff_label = '';
    public $staff_label_plural = '';
    public $shop_label = '';
    public $service_label = '';

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

    public function mount(?BarberShop $barberShop = null) {
        if (!$barberShop || !$barberShop->id) {
            $barberShop = auth()->user()?->barberShop;
            if (!$barberShop) {
                $barberShop = BarberShop::first();
            }
        }

        $this->item = $barberShop;
        $this->fill($barberShop->toArray());
        $this->trial_ends_at = $barberShop->trial_ends_at?->format('Y-m-d\TH:i');
        $this->expires_at = $barberShop->expires_at?->format('Y-m-d\TH:i');
        $this->min_service_time = $barberShop->min_service_time ?? '';
        $this->business_type = $barberShop->business_type ?? 'barbershop';
        $this->staff_label = $barberShop->staff_label ?? '';
        $this->staff_label_plural = $barberShop->staff_label_plural ?? '';
        $this->shop_label = $barberShop->shop_label ?? '';
        $this->service_label = $barberShop->service_label ?? '';
    }

    public function render() {
        return view('livewire.admin.barber-shops.edit', [
            'owners' => $this->getownersList(),
        ])->layout('components.layouts.app');
    }

    public function update(UpdateBarberShopAction $action) {
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
        ]);
        $action->execute($this->item, $dto);
        session()->flash('success', __('barber-shops.updated'));
        return to_route('admin.barber-shops.index');
    }

    protected function rules(): array {
        $rules = BarberShop::rules($this->item->id);
        if ($this->logo instanceof \Illuminate\Http\UploadedFile) { $rules['logo'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->logo) && $this->logo !== '') { $rules['logo'] = ['nullable', 'string', 'max:255']; } else { $rules['logo'] = ['nullable']; }
        if ($this->banner instanceof \Illuminate\Http\UploadedFile) { $rules['banner'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->banner) && $this->banner !== '') { $rules['banner'] = ['nullable', 'string', 'max:255']; } else { $rules['banner'] = ['nullable']; }
        return $rules;
    }
}
