<?php

namespace App\Domain\ShopFrontPageSetting\Actions;

use App\Models\ShopFrontPageSetting;
use App\Models\AuditTrail;

class DeleteShopFrontPageSettingAction
{
    public function execute(ShopFrontPageSetting $model): bool 
    {
        AuditTrail::log($model, 'delete', 'ShopFrontPageSettings');
        return $model->delete(); 
    }
}