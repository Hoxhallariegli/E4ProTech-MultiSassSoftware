<?php

namespace App\Domain\Report\Actions;

use App\Models\Report;
use App\Models\AuditTrail;

class DeleteReportAction
{
    public function execute(Report $model): bool 
    {
        AuditTrail::log($model, 'delete', 'Reports');
        return $model->delete(); 
    }
}