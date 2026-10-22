<?php

namespace App\Domain\BulkCancellation\Actions;

use App\Models\BulkCancellation;
use App\Models\AuditTrail;

class DeleteBulkCancellationAction
{
    public function execute(BulkCancellation $model): bool 
    {
        AuditTrail::log($model, 'delete', 'BulkCancellations');
        return $model->delete(); 
    }
}