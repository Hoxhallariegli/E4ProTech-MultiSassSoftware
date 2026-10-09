<?php

namespace App\Domain\TestModule\Actions;

use App\Models\TestModule;
use App\Domain\TestModule\DTOs\TestModuleDTO;
use App\Models\AuditTrail;

class UpdateTestModuleAction
{
    public function execute(TestModule $model, TestModuleDTO $dto): TestModule
    {
        $data = $dto->toArray();
        if ($model->image && isset($data['image']) && $data['image'] !== $model->image) { app(\App\Services\ImageUploadService::class)->delete($model->image); }
        if ($model->cover_photo && isset($data['cover_photo']) && $data['cover_photo'] !== $model->cover_photo) { app(\App\Services\ImageUploadService::class)->delete($model->cover_photo); }
        $model->fill($data);
        AuditTrail::log($model, 'update', 'TestModules');
        $model->save();
        return $model->fresh();
    }
}