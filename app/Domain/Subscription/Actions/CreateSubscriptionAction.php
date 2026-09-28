<?php

namespace App\Domain\Subscription\Actions;

use App\Models\Subscription;
use App\Domain\Subscription\DTOs\SubscriptionDTO;
use App\Models\AuditTrail;

class CreateSubscriptionAction
{
    public function execute(SubscriptionDTO $dto): Subscription 
    {
        $item = Subscription::create($dto->toArray());
        AuditTrail::log($item, 'create', 'Subscriptions');
        return $item;
    }
}