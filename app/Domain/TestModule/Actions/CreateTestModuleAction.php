<?php

namespace App\Domain\TestModule\Actions;

use App\Models\TestModule;
use App\Domain\TestModule\DTOs\TestModuleDTO;
use App\Models\AuditTrail;

class CreateTestModuleAction
{
    public function execute(TestModuleDTO $dto): TestModule 
    {
        $item = TestModule::create($dto->toArray());
        AuditTrail::log($item, 'create', 'TestModules');
        return $item;
    }
}