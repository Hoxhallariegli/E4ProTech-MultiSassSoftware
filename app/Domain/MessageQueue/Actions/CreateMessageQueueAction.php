<?php

namespace App\Domain\MessageQueue\Actions;

use App\Models\MessageQueue;
use App\Domain\MessageQueue\DTOs\MessageQueueDTO;
use App\Models\AuditTrail;

class CreateMessageQueueAction
{
    public function execute(MessageQueueDTO $dto): MessageQueue 
    {
        $item = MessageQueue::create($dto->toArray());
        AuditTrail::log($item, 'create', 'MessageQueues');
        return $item;
    }
}