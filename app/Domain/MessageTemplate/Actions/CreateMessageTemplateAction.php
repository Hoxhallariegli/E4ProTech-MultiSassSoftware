<?php

namespace App\Domain\MessageTemplate\Actions;

use App\Models\MessageTemplate;
use App\Domain\MessageTemplate\DTOs\MessageTemplateDTO;
use App\Models\AuditTrail;

class CreateMessageTemplateAction
{
    public function execute(MessageTemplateDTO $dto): MessageTemplate 
    {
        $item = MessageTemplate::create($dto->toArray());
        AuditTrail::log($item, 'create', 'MessageTemplates');
        return $item;
    }
}