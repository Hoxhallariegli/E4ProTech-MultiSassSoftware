<?php

namespace App\Domain\Plan\Actions;

use App\Models\Plan;
use App\Domain\Plan\DTOs\PlanDTO;
use App\Models\AuditTrail;

class UpdatePlanAction
{
    public function execute(Plan $model, PlanDTO $dto): Plan
    {
        $data = $dto->toArray();
        $model->fill($data);
        AuditTrail::log($model, 'update', 'Plans');
        $model->save();
        return $model->fresh();
    }
}