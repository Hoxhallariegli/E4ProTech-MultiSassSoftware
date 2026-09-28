<?php

namespace App\Domain\NotificationChannel\Actions;

use App\Models\NotificationChannel;
use App\Domain\NotificationChannel\DTOs\NotificationChannelDTO;
use App\Models\AuditTrail;

class CreateNotificationChannelAction
{
    public function execute(NotificationChannelDTO $dto): NotificationChannel 
    {
        $item = NotificationChannel::create($dto->toArray());
        AuditTrail::log($item, 'create', 'NotificationChannels');
        return $item;
    }
}