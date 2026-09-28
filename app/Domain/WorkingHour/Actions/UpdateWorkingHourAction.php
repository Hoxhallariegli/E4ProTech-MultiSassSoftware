<?php

namespace App\Domain\WorkingHour\Actions;

use App\Models\WorkingHour;
use App\Domain\WorkingHour\DTOs\WorkingHourDTO;
use App\Models\AuditTrail;

class UpdateWorkingHourAction
{
    public function execute(WorkingHour $model, WorkingHourDTO $dto): WorkingHour
    {
        $data = $dto->toArray();
        $model->fill($data);
        AuditTrail::log($model, 'update', 'WorkingHours');
        $model->save();
        return $model->fresh();
    }
}