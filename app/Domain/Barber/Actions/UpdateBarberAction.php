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
        $dirty = $model->getDirty();
        $original = $model->getOriginal();
        $model->save();

        if (!empty($dirty)) {
            AuditTrail::create([
                'user_id' => auth()->id(),
                'title' => "Update Barber",
                'reference_id' => (string)$model->id,
                'section' => 'Barbers',
                'type' => 'update',
                'old_values' => array_intersect_key($original, $dirty),
                'new_values' => $dirty,
            ]);
        }

        return $model->fresh();
    }
}
