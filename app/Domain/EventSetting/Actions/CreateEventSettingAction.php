<?php

namespace App\Domain\EventSetting\Actions;

use App\Models\EventSetting;
use App\Domain\EventSetting\DTOs\EventSettingDTO;
use App\Models\AuditTrail;

class CreateEventSettingAction
{
    public function execute(EventSettingDTO $dto): EventSetting 
    {
        $item = EventSetting::create($dto->toArray());
        AuditTrail::log($item, 'create', 'EventSettings');
        return $item;
    }
}