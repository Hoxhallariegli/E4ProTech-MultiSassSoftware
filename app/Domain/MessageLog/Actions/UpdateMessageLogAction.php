<?php

namespace App\Domain\MessageLog\Actions;

use App\Models\MessageLog;
use App\Domain\MessageLog\DTOs\MessageLogDTO;
use App\Models\AuditTrail;

class UpdateMessageLogAction
{
    public function execute(MessageLog $model, MessageLogDTO $dto): MessageLog
    {
        $data = $dto->toArray();
        $model->fill($data);
        AuditTrail::log($model, 'update', 'MessageLogs');
        $model->save();
        return $model->fresh();
    }
}