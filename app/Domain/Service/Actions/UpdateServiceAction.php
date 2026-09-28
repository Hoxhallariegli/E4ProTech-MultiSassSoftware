<?php

namespace App\Domain\Service\Actions;

use App\Models\Service;
use App\Domain\Service\DTOs\ServiceDTO;
use App\Models\AuditTrail;

class UpdateServiceAction
{
    public function execute(Service $model, ServiceDTO $dto): Service
    {
        $data = $dto->toArray();
        $model->fill($data);
        AuditTrail::log($model, 'update', 'Services');
        $model->save();
        return $model->fresh();
    }
}