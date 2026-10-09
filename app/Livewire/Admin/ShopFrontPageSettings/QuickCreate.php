<?php

namespace App\Livewire\Admin\ShopFrontPageSettings;

use App\Models\ShopFrontPageSetting;
use App\Domain\ShopFrontPageSetting\DTOs\ShopFrontPageSettingDTO;
use App\Domain\ShopFrontPageSetting\Actions\CreateShopFrontPageSettingAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class QuickCreate extends Component
{
        use WithPagination;
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

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.shop-front-page-settings.quick-create', [
            'barberShops' => $this->getbarberShopsList(),
        ]); }

    public function store(CreateShopFrontPageSettingAction $action)
    {
        $this->validate();
        $dto = ShopFrontPageSettingDTO::fromArray([
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
        ]);
        $item = $action->execute($dto);
        $this->dispatch('shop-front-page-setting-created', id: $item->id);
        $this->js("Livewire.dispatch('shop-front-page-setting-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('shop-front-page-settings.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['hero_title', 'hero_subtitle', 'hero_button_text', 'services_badge_text', 'services_title', 'staff_badge_text', 'staff_title', 'contact_phone', 'contact_email', 'contact_address', 'google_maps_url', 'footer_text', 'barber_shop_id']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = ShopFrontPageSetting::rules();
        return $rules; }
}