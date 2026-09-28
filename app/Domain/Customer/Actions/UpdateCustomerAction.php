<?php

namespace App\Domain\Customer\Actions;

use App\Models\Customer;
use App\Domain\Customer\DTOs\CustomerDTO;
use App\Models\AuditTrail;

class UpdateCustomerAction
{
    public function execute(Customer $model, CustomerDTO $dto): Customer
    {
        $data = $dto->toArray();
        if ($model->photo && isset($data['photo']) && $data['photo'] !== $model->photo) { app(\App\Services\ImageUploadService::class)->delete($model->photo); }
        $model->fill($data);
        AuditTrail::log($model, 'update', 'Customers');
        $model->save();
        return $model->fresh();
    }
}