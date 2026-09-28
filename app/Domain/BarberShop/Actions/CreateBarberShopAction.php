<?php

namespace App\Domain\BarberShop\Actions;

use App\Models\BarberShop;
use App\Domain\BarberShop\DTOs\BarberShopDTO;
use App\Models\AuditTrail;

class CreateBarberShopAction
{
    public function execute(BarberShopDTO $dto): BarberShop 
    {
        $item = BarberShop::create($dto->toArray());
        AuditTrail::log($item, 'create', 'BarberShops');
        return $item;
    }
}