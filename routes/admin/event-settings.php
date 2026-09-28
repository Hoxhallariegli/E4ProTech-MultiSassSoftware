<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\EventSettings\EventSettings;
use App\Livewire\Admin\EventSettings\Create;
use App\Livewire\Admin\EventSettings\Edit;
Route::prefix('event-settings')->group(function () {
    Route::get('/', EventSettings::class)->name('admin.event-settings.index');
    Route::get('create', Create::class)->name('admin.event-settings.create');
    Route::get('/{' . 'eventSetting' . '}/edit', Edit::class)->name('admin.event-settings.edit');
});