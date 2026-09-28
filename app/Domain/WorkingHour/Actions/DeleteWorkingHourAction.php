<?php

namespace App\Domain\WorkingHour\Actions;

use App\Models\WorkingHour;
use App\Models\AuditTrail;

class DeleteWorkingHourAction
{
    public function execute(WorkingHour $model): bool 
    {
        AuditTrail::log($model, 'delete', 'WorkingHours');
        return $model->delete(); 
    }
}