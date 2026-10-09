<?php

namespace App\Domain\ShopFrontPageSetting\Actions;

use App\Models\ShopFrontPageSetting;
use App\Domain\ShopFrontPageSetting\DTOs\ShopFrontPageSettingDTO;
use App\Models\AuditTrail;

class CreateShopFrontPageSettingAction
{
    public function execute(ShopFrontPageSettingDTO $dto): ShopFrontPageSetting 
    {
        $item = ShopFrontPageSetting::create($dto->toArray());
        AuditTrail::log($item, 'create', 'ShopFrontPageSettings');
        return $item;
    }
}