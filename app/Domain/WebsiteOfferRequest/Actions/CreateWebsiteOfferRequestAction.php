<?php

namespace App\Domain\WebsiteOfferRequest\Actions;

use App\Models\WebsiteOfferRequest;
use App\Domain\WebsiteOfferRequest\DTOs\WebsiteOfferRequestDTO;
use App\Models\AuditTrail;

class CreateWebsiteOfferRequestAction
{
    public function execute(WebsiteOfferRequestDTO $dto): WebsiteOfferRequest
    {
        $item = WebsiteOfferRequest::create($dto->toArray());

        AuditTrail::logCustom($item, 'create', 'Subscriptions', "Kërkesë për Uebsajt ({$item->desired_domain})");

        return $item;
    }
}
