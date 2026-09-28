<?php

namespace App\Domain\Service\Actions;

use App\Models\Service;
use App\Domain\Service\DTOs\ServiceDTO;
use App\Models\AuditTrail;

class CreateServiceAction
{
    public function execute(ServiceDTO $dto): Service 
    {
        $item = Service::create($dto->toArray());
        AuditTrail::log($item, 'create', 'Services');
        return $item;
    }
}