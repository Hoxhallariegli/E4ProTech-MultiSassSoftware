<?php

namespace App\Domain\BarberShop\Actions;

use App\Models\BarberShop;
use App\Models\AuditTrail;

class DeleteBarberShopAction
{
    public function execute(BarberShop $model): bool 
    {
        if ($model->logo) { app(\App\Services\ImageUploadService::class)->delete($model->logo); }
        if ($model->banner) { app(\App\Services\ImageUploadService::class)->delete($model->banner); }
        AuditTrail::log($model, 'delete', 'BarberShops');
        return $model->delete(); 
    }
}