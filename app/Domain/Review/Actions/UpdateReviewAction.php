<?php

namespace App\Domain\Review\Actions;

use App\Models\Review;
use App\Domain\Review\DTOs\ReviewDTO;
use App\Models\AuditTrail;

class UpdateReviewAction
{
    public function execute(Review $model, ReviewDTO $dto): Review
    {
        $data = $dto->toArray();
        $model->fill($data);
        AuditTrail::log($model, 'update', 'Reviews');
        $model->save();
        return $model->fresh();
    }
}