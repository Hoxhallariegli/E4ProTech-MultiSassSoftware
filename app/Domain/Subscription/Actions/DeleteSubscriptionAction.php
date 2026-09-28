<?php

namespace App\Domain\Subscription\Actions;

use App\Models\Subscription;
use App\Models\AuditTrail;

class DeleteSubscriptionAction
{
    public function execute(Subscription $model): bool 
    {
        AuditTrail::log($model, 'delete', 'Subscriptions');
        return $model->delete(); 
    }
}