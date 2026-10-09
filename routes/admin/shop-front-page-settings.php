<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\ShopFrontPageSettings\ShopFrontPageSettings;
use App\Livewire\Admin\ShopFrontPageSettings\Create;
use App\Livewire\Admin\ShopFrontPageSettings\Edit;
Route::prefix('shop-front-page-settings')->group(function () {
    Route::get('/', ShopFrontPageSettings::class)->name('admin.shop-front-page-settings.index');
    Route::get('create', Create::class)->name('admin.shop-front-page-settings.create');
    Route::get('/{' . 'shopFrontPageSetting' . '}/edit', Edit::class)->name('admin.shop-front-page-settings.edit');
});