<?php

namespace App\Domain\WorkingHour\Actions;

use App\Models\WorkingHour;
use App\Domain\WorkingHour\DTOs\WorkingHourDTO;
use App\Models\AuditTrail;

class CreateWorkingHourAction
{
    public function execute(WorkingHourDTO $dto): WorkingHour 
    {
        $item = WorkingHour::create($dto->toArray());
        AuditTrail::log($item, 'create', 'WorkingHours');
        return $item;
    }
}