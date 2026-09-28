<?php

namespace App\Domain\MessageLog\Actions;

use App\Models\MessageLog;
use App\Models\AuditTrail;

class DeleteMessageLogAction
{
    public function execute(MessageLog $model): bool 
    {
        AuditTrail::log($model, 'delete', 'MessageLogs');
        return $model->delete(); 
    }
}