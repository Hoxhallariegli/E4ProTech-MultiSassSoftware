<?php

namespace App\Domain\Service\Actions;

use App\Models\Service;
use App\Domain\Service\DTOs\ServiceDTO;
use App\Models\AuditTrail;

class UpdateServiceAction
{
    public function execute(Service $model, ServiceDTO $dto): Service
    {
        $data = $dto->toArray();
        $model->fill($data);

        $dirty = $model->getDirty();
        $original = $model->getOriginal();

        $model->save();

        if (!empty($dirty)) {
            AuditTrail::create([
                'user_id' => auth()->id(),
                'title' => "Update Service",
                'reference_id' => (string)$model->id,
                'section' => 'Services',
                'type' => 'update',
                'old_values' => array_intersect_key($original, $dirty),
                'new_values' => $dirty,
            ]);
        }

        return $model->fresh();
    }
}
