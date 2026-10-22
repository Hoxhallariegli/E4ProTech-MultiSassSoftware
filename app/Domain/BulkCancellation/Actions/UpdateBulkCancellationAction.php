<?php

namespace App\Domain\BulkCancellation\Actions;

use App\Models\BulkCancellation;
use App\Domain\BulkCancellation\DTOs\BulkCancellationDTO;
use App\Models\AuditTrail;

class UpdateBulkCancellationAction
{
    public function execute(BulkCancellation $model, BulkCancellationDTO $dto): BulkCancellation
    {
        $data = $dto->toArray();
        $model->fill($data);
        AuditTrail::log($model, 'update', 'BulkCancellations');
        $model->save();
        return $model->fresh();
    }
}