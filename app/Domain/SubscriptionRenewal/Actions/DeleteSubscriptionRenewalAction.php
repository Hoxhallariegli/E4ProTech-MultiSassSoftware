<?php

namespace App\Domain\SubscriptionRenewal\Actions;

use App\Models\SubscriptionRenewal;
use App\Models\AuditTrail;

class DeleteSubscriptionRenewalAction
{
    public function execute(SubscriptionRenewal $model): bool 
    {
        if ($model->transfer_document) { app(\App\Services\ImageUploadService::class)->delete($model->transfer_document); }
        AuditTrail::log($model, 'delete', 'SubscriptionRenewals');
        return $model->delete(); 
    }
}