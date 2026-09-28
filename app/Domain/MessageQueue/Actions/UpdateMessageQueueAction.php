<?php

namespace App\Domain\MessageQueue\Actions;

use App\Models\MessageQueue;
use App\Domain\MessageQueue\DTOs\MessageQueueDTO;
use App\Models\AuditTrail;

class UpdateMessageQueueAction
{
    public function execute(MessageQueue $model, MessageQueueDTO $dto): MessageQueue
    {
        $data = $dto->toArray();
        $model->fill($data);
        AuditTrail::log($model, 'update', 'MessageQueues');
        $model->save();
        return $model->fresh();
    }
}