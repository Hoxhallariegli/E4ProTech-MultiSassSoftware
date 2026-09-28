<?php

namespace App\Domain\MessageTemplate\Actions;

use App\Models\MessageTemplate;
use App\Models\AuditTrail;

class DeleteMessageTemplateAction
{
    public function execute(MessageTemplate $model): bool 
    {
        AuditTrail::log($model, 'delete', 'MessageTemplates');
        return $model->delete(); 
    }
}