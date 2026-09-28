<?php

namespace App\Domain\MessageQueue\Actions;

use App\Models\MessageQueue;
use App\Models\AuditTrail;

class DeleteMessageQueueAction
{
    public function execute(MessageQueue $model): bool 
    {
        AuditTrail::log($model, 'delete', 'MessageQueues');
        return $model->delete(); 
    }
}