<?php

namespace App\Domain\Plan\Actions;

use App\Models\Plan;
use App\Domain\Plan\DTOs\PlanDTO;
use App\Models\AuditTrail;

class CreatePlanAction
{
    public function execute(PlanDTO $dto): Plan 
    {
        $item = Plan::create($dto->toArray());
        AuditTrail::log($item, 'create', 'Plans');
        return $item;
    }
}