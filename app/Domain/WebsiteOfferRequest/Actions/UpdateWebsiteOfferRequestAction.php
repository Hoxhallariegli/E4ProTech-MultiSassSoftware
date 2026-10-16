<?php

namespace App\Domain\WebsiteOfferRequest\Actions;

use App\Models\WebsiteOfferRequest;
use App\Domain\WebsiteOfferRequest\DTOs\WebsiteOfferRequestDTO;
use App\Models\AuditTrail;

class UpdateWebsiteOfferRequestAction
{
    public function execute(WebsiteOfferRequest $model, WebsiteOfferRequestDTO $dto): WebsiteOfferRequest
    {
        $model->fill($dto->toArray());
        $dirty = $model->getDirty();
        $original = $model->getOriginal();
        $model->save();

        if (!empty($dirty)) {
            AuditTrail::create([
                'user_id' => auth()->id(),
                'title' => "Update WebsiteOfferRequest #{$model->id}",
                'reference_id' => (string)$model->id,
                'section' => 'Subscriptions',
                'type' => 'update',
                'old_values' => array_intersect_key($original, $dirty),
                'new_values' => $dirty,
            ]);
        }

        return $model->fresh();
    }
}
