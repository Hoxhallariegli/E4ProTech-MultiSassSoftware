<?php

namespace App\Domain\Barber\Actions;

use App\Models\Barber;
use App\Domain\Barber\DTOs\BarberDTO;
use App\Models\AuditTrail;

class UpdateBarberAction
{
    public function execute(Barber $model, BarberDTO $dto): Barber
    {
        $data = $dto->toArray();
        if ($model->photo && isset($data['photo']) && $data['photo'] !== $model->photo) { app(\App\Services\ImageUploadService::class)->delete($model->photo); }
        $model->fill($data);
        AuditTrail::log($model, 'update', 'Barbers');
        $model->save();
        return $model->fresh();
    }
}