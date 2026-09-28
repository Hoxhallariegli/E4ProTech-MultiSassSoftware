<?php

namespace App\Domain\Plan\Actions;

use App\Models\Plan;
use App\Models\AuditTrail;

class DeletePlanAction
{
    public function execute(Plan $model): bool 
    {
        AuditTrail::log($model, 'delete', 'Plans');
        return $model->delete(); 
    }
}