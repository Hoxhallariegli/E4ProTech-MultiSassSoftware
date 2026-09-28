<?php

namespace App\Domain\MessageTemplate\Actions;

use App\Models\MessageTemplate;
use App\Domain\MessageTemplate\DTOs\MessageTemplateDTO;
use App\Models\AuditTrail;

class UpdateMessageTemplateAction
{
    public function execute(MessageTemplate $model, MessageTemplateDTO $dto): MessageTemplate
    {
        $data = $dto->toArray();
        $model->fill($data);
        AuditTrail::log($model, 'update', 'MessageTemplates');
        $model->save();
        return $model->fresh();
    }
}