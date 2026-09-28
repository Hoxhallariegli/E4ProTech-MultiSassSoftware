<?php

namespace App\Domain\Review\Actions;

use App\Models\Review;
use App\Models\AuditTrail;

class DeleteReviewAction
{
    public function execute(Review $model): bool 
    {
        AuditTrail::log($model, 'delete', 'Reviews');
        return $model->delete(); 
    }
}