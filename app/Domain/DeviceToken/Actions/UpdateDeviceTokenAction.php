<?php

namespace App\Domain\DeviceToken\Actions;

use App\Models\DeviceToken;
use App\Domain\DeviceToken\DTOs\DeviceTokenDTO;
use App\Models\AuditTrail;

class UpdateDeviceTokenAction
{
    public function execute(DeviceToken $model, DeviceTokenDTO $dto): DeviceToken
    {
        $data = $dto->toArray();
        $model->fill($data);
        AuditTrail::log($model, 'update', 'DeviceTokens');
        $model->save();
        return $model->fresh();
    }
}