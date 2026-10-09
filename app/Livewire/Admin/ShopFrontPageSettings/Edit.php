<?php

namespace App\Livewire\Admin\ShopFrontPageSettings;

use App\Models\ShopFrontPageSetting;
use App\Domain\ShopFrontPageSetting\DTOs\ShopFrontPageSettingDTO;
use App\Domain\ShopFrontPageSetting\Actions\UpdateShopFrontPageSettingAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit ShopFrontPageSetting')]
class Edit extends Component
{
        use WithPagination;
 public ShopFrontPageSetting $item;
    public $hero_title = '';
    public $hero_subtitle = '';
    public $hero_button_text = '';
    public $services_badge_text = '';
    public $services_title = '';
    public $staff_badge_text = '';
    public $staff_title = '';
    public $contact_phone = '';
    public $contact_email = '';
    public $contact_address = '';
    public $google_maps_url = '';
    public $footer_text = '';
    public $barber_shop_id = '';
 
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

    public function mount(ShopFrontPageSetting $shopFrontPageSetting) { $this->item = $shopFrontPageSetting; $this->fill($shopFrontPageSetting->toArray());  }
    public function render() { abort_if_cannot('edit_shop_front_page_settings'); return view('livewire.admin.shop-front-page-settings.edit', [
            'barberShops' => $this->getbarberShopsList(),
        ])->layout('components.layouts.app'); }
    public function update(UpdateShopFrontPageSettingAction $action) { $this->validate();  $dto = ShopFrontPageSettingDTO::fromArray([
            'hero_title' => $this->hero_title,
            'hero_subtitle' => $this->hero_subtitle,
            'hero_button_text' => $this->hero_button_text,
            'services_badge_text' => $this->services_badge_text,
            'services_title' => $this->services_title,
            'staff_badge_text' => $this->staff_badge_text,
            'staff_title' => $this->staff_title,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'contact_address' => $this->contact_address,
            'google_maps_url' => $this->google_maps_url,
            'footer_text' => $this->footer_text,
            'barber_shop_id' => $this->barber_shop_id,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('shop-front-page-settings.updated')); return to_route('admin.shop-front-page-settings.index'); }
    protected function rules(): array { $rules = ShopFrontPageSetting::rules($this->item->id);
        return $rules; }
}