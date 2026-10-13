<?php

namespace App\Domain\Report\Actions;

use App\Models\Report;
use App\Domain\Report\DTOs\ReportDTO;
use App\Models\AuditTrail;

class UpdateReportAction
{
    public function execute(Report $model, ReportDTO $dto): Report
    {
        $data = $dto->toArray();
        $model->fill($data);
        AuditTrail::log($model, 'update', 'Reports');
        $model->save();
        return $model->fresh();
    }
}