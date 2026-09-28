<?php

namespace App\Domain\Barber\Actions;

use App\Models\Barber;
use App\Domain\Barber\DTOs\BarberDTO;
use App\Models\AuditTrail;

class CreateBarberAction
{
    public function execute(BarberDTO $dto): Barber 
    {
        $item = Barber::create($dto->toArray());
        AuditTrail::log($item, 'create', 'Barbers');
        return $item;
    }
}