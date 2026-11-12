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

    public function forceExecute(Barber $model): bool
    {
        if ($model->photo) {
            app(\App\Services\ImageUploadService::class)->delete($model->photo);
        }

        // Clean up or delete all dependent/child relations
        \App\Models\Booking::withoutGlobalScope('barber_shop_access')->where('barber_id', $model->id)->delete();
        \App\Models\WorkingHour::withoutGlobalScope('barber_shop_access')->where('barber_id', $model->id)->delete();
        \App\Models\BulkCancellation::withoutGlobalScope('barber_shop_access')->where('barber_id', $model->id)->delete();


        return $model->forceDelete();
    }
}
