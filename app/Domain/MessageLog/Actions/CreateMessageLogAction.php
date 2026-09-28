<?php

namespace App\Domain\MessageLog\Actions;

use App\Models\MessageLog;
use App\Domain\MessageLog\DTOs\MessageLogDTO;
use App\Models\AuditTrail;

class CreateMessageLogAction
{
    public function execute(MessageLogDTO $dto): MessageLog 
    {
        $item = MessageLog::create($dto->toArray());
        AuditTrail::log($item, 'create', 'MessageLogs');
        return $item;
    }
}