<?php

namespace App\Livewire\Admin\ShopFrontPageSettings;

use App\Models\ShopFrontPageSetting;
use Livewire\Component;

class Row extends Component { public ShopFrontPageSetting $item; public function render() { return view('livewire.admin.shop-front-page-settings.row'); } }