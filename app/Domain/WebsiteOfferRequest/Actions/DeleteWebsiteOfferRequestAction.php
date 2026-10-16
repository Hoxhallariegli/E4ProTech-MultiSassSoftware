<?php

namespace App\Domain\WebsiteOfferRequest\Actions;

use App\Models\WebsiteOfferRequest;
use App\Models\AuditTrail;

class DeleteWebsiteOfferRequestAction
{
    public function execute(WebsiteOfferRequest $model): void
    {
        AuditTrail::logCustom($model, 'delete', 'Subscriptions', "Delete WebsiteOfferRequest #{$model->id}");
        $model->delete();
    }
}
