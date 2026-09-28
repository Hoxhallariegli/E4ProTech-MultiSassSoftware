<?php

namespace App\Domain\Customer\Actions;

use App\Models\Customer;
use App\Models\AuditTrail;

class DeleteCustomerAction
{
    public function execute(Customer $model): bool 
    {
        if ($model->photo) { app(\App\Services\ImageUploadService::class)->delete($model->photo); }
        AuditTrail::log($model, 'delete', 'Customers');
        return $model->delete(); 
    }
}