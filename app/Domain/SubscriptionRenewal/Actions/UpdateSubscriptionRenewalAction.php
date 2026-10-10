<?php

namespace App\Domain\SubscriptionRenewal\Actions;

use App\Models\SubscriptionRenewal;
use App\Domain\SubscriptionRenewal\DTOs\SubscriptionRenewalDTO;
use App\Models\AuditTrail;

class UpdateSubscriptionRenewalAction
{
    public function execute(SubscriptionRenewal $model, SubscriptionRenewalDTO $dto): SubscriptionRenewal
    {
        $data = $dto->toArray();
        if ($model->transfer_document && isset($data['transfer_document']) && $data['transfer_document'] !== $model->transfer_document) { app(\App\Services\ImageUploadService::class)->delete($model->transfer_document); }
        $model->fill($data);
        AuditTrail::log($model, 'update', 'SubscriptionRenewals');
        $model->save();
        return $model->fresh();
    }
}