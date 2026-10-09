<?php

namespace App\Domain\ShopFrontPageSetting\Actions;

use App\Models\ShopFrontPageSetting;
use App\Domain\ShopFrontPageSetting\DTOs\ShopFrontPageSettingDTO;
use App\Models\AuditTrail;

class UpdateShopFrontPageSettingAction
{
    public function execute(ShopFrontPageSetting $model, ShopFrontPageSettingDTO $dto): ShopFrontPageSetting
    {
        $data = $dto->toArray();
        $model->fill($data);
        AuditTrail::log($model, 'update', 'ShopFrontPageSettings');
        $model->save();
        return $model->fresh();
    }
}