<?php

namespace App\Domain\Subscription\Actions;

use App\Models\Subscription;
use App\Domain\Subscription\DTOs\SubscriptionDTO;
use App\Models\AuditTrail;

class UpdateSubscriptionAction
{
    public function execute(Subscription $model, SubscriptionDTO $dto): Subscription
    {
        $data = $dto->toArray();
        $model->fill($data);
        AuditTrail::log($model, 'update', 'Subscriptions');
        $model->save();
        return $model->fresh();
    }
}