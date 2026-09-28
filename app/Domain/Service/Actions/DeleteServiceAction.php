<?php

namespace App\Domain\Service\Actions;

use App\Models\Service;
use App\Models\AuditTrail;

class DeleteServiceAction
{
    public function execute(Service $model): bool 
    {
        AuditTrail::log($model, 'delete', 'Services');
        return $model->delete(); 
    }
}