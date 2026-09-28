<?php

namespace App\Domain\DeviceToken\Actions;

use App\Models\DeviceToken;
use App\Models\AuditTrail;

class DeleteDeviceTokenAction
{
    public function execute(DeviceToken $model): bool 
    {
        AuditTrail::log($model, 'delete', 'DeviceTokens');
        return $model->delete(); 
    }
}