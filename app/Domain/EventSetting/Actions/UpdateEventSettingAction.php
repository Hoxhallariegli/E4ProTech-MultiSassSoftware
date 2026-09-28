<?php

namespace App\Domain\EventSetting\Actions;

use App\Models\EventSetting;
use App\Domain\EventSetting\DTOs\EventSettingDTO;
use App\Models\AuditTrail;

class UpdateEventSettingAction
{
    public function execute(EventSetting $model, EventSettingDTO $dto): EventSetting
    {
        $data = $dto->toArray();
        $model->fill($data);
        AuditTrail::log($model, 'update', 'EventSettings');
        $model->save();
        return $model->fresh();
    }
}