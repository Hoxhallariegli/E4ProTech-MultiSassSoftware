<?php

namespace App\Domain\NotificationChannel\Actions;

use App\Models\NotificationChannel;
use App\Models\AuditTrail;

class DeleteNotificationChannelAction
{
    public function execute(NotificationChannel $model): bool 
    {
        AuditTrail::log($model, 'delete', 'NotificationChannels');
        return $model->delete(); 
    }
}