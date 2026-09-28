<?php

namespace App\Domain\NotificationChannel\Actions;

use App\Models\NotificationChannel;
use App\Domain\NotificationChannel\DTOs\NotificationChannelDTO;
use App\Models\AuditTrail;

class UpdateNotificationChannelAction
{
    public function execute(NotificationChannel $model, NotificationChannelDTO $dto): NotificationChannel
    {
        $data = $dto->toArray();
        $model->fill($data);
        AuditTrail::log($model, 'update', 'NotificationChannels');
        $model->save();
        return $model->fresh();
    }
}