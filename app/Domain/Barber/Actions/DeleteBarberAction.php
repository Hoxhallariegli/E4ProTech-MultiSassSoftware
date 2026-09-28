<?php

namespace App\Domain\Barber\Actions;

use App\Models\Barber;
use App\Models\AuditTrail;

class DeleteBarberAction
{
    public function execute(Barber $model): bool 
    {
        if ($model->photo) { app(\App\Services\ImageUploadService::class)->delete($model->photo); }
        AuditTrail::log($model, 'delete', 'Barbers');
        return $model->delete(); 
    }
}