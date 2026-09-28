<?php

namespace App\Domain\Review\Actions;

use App\Models\Review;
use App\Domain\Review\DTOs\ReviewDTO;
use App\Models\AuditTrail;

class CreateReviewAction
{
    public function execute(ReviewDTO $dto): Review 
    {
        $item = Review::create($dto->toArray());
        AuditTrail::log($item, 'create', 'Reviews');
        return $item;
    }
}