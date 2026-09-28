<?php

namespace App\Domain\EventSetting\Actions;

use App\Models\EventSetting;
use App\Models\AuditTrail;

class DeleteEventSettingAction
{
    public function execute(EventSetting $model): bool 
    {
        AuditTrail::log($model, 'delete', 'EventSettings');
        return $model->delete(); 
    }
}