<?php

namespace App\Domain\TestModule\Actions;

use App\Models\TestModule;
use App\Models\AuditTrail;

class DeleteTestModuleAction
{
    public function execute(TestModule $model): bool 
    {
        if ($model->image) { app(\App\Services\ImageUploadService::class)->delete($model->image); }
        if ($model->cover_photo) { app(\App\Services\ImageUploadService::class)->delete($model->cover_photo); }
        AuditTrail::log($model, 'delete', 'TestModules');
        return $model->delete(); 
    }
}