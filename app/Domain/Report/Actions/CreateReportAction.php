<?php

namespace App\Domain\Report\Actions;

use App\Models\Report;
use App\Domain\Report\DTOs\ReportDTO;
use App\Models\AuditTrail;

class CreateReportAction
{
    public function execute(ReportDTO $dto): Report 
    {
        $item = Report::create($dto->toArray());
        AuditTrail::log($item, 'create', 'Reports');
        return $item;
    }
}