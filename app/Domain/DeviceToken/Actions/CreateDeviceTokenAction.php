<?php

namespace App\Domain\DeviceToken\Actions;

use App\Models\DeviceToken;
use App\Domain\DeviceToken\DTOs\DeviceTokenDTO;
use App\Models\AuditTrail;

class CreateDeviceTokenAction
{
    public function execute(DeviceTokenDTO $dto): DeviceToken 
    {
        $item = DeviceToken::create($dto->toArray());
        AuditTrail::log($item, 'create', 'DeviceTokens');
        return $item;
    }
}