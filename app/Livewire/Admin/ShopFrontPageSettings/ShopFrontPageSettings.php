<?php

namespace App\Livewire\Admin\ShopFrontPageSettings;

use App\Models\ShopFrontPageSetting;
use App\Domain\ShopFrontPageSetting\Queries\ShopFrontPageSettingListQuery;
use App\Domain\ShopFrontPageSetting\Actions\DeleteShopFrontPageSettingAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('ShopFrontPageSettings')]
class ShopFrontPageSettings extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $barber_shop_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'barber_shop_id', ]); $this->resetPage(); }

    #[On('echo-private:mobile.shop-front-page-settings,.shop-front-page-settings.changed')]
    public function onBroadcastCloudChange($event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        $this->render();
    }

    public function render()
    {
        abort_if_cannot('view_shop_front_page_settings');
        $query = (new ShopFrontPageSettingListQuery())->handle(['search' => $this->search,             'barber_shop_id' => $this->barber_shop_id,
], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.shop-front-page-settings.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => ShopFrontPageSetting::sortable(),
            'barberShops' => \App\Models\BarberShop::pluck('name', 'id')->toArray(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, ShopFrontPageSetting::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteShopFrontPageSetting($id, DeleteShopFrontPageSettingAction $action) 
    {
        abort_if_cannot('delete_shop_front_page_settings');
        $item = ShopFrontPageSetting::find($id);
        if (!$item) { $this->dispatch('toast', message: __('shop-front-page-settings.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('shop-front-page-settings.deleted'), type: 'success'); $this->resetPage(); } 
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('shop-front-page-settings.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('shop-front-page-settings.delete_error'), type: 'error'); }
    }
}