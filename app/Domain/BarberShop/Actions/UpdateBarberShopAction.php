<?php

namespace App\Domain\BarberShop\Actions;

use App\Models\BarberShop;
use App\Domain\BarberShop\DTOs\BarberShopDTO;
use App\Models\AuditTrail;

class UpdateBarberShopAction
{
    public function execute(BarberShop $model, BarberShopDTO $dto): BarberShop
    {
        $data = $dto->toArray();
        if ($model->logo && isset($data['logo']) && $data['logo'] !== $model->logo) { app(\App\Services\ImageUploadService::class)->delete($model->logo); }
        if ($model->banner && isset($data['banner']) && $data['banner'] !== $model->banner) { app(\App\Services\ImageUploadService::class)->delete($model->banner); }
        $model->fill($data);
        $dirty = $model->getDirty();
        $original = $model->getOriginal();
        $model->save();

        if (!empty($dirty)) {
            AuditTrail::create([
                'user_id' => auth()->id(),
                'title' => "Update BarberShop",
                'reference_id' => (string)$model->id,
                'section' => 'BarberShops',
                'type' => 'update',
                'old_values' => array_intersect_key($original, $dirty),
                'new_values' => $dirty,
            ]);
        }

        return $model->fresh();
    }
}
