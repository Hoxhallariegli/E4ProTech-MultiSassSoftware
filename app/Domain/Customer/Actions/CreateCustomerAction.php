<?php

namespace App\Domain\Customer\Actions;

use App\Models\Customer;
use App\Domain\Customer\DTOs\CustomerDTO;
use App\Models\AuditTrail;

class CreateCustomerAction
{
    public function execute(CustomerDTO $dto): Customer 
    {
        $item = Customer::create($dto->toArray());
        AuditTrail::log($item, 'create', 'Customers');
        return $item;
    }
}