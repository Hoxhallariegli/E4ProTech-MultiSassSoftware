<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\NotificationChannels\NotificationChannels;
use App\Livewire\Admin\NotificationChannels\Create;
use App\Livewire\Admin\NotificationChannels\Edit;
Route::prefix('notification-channels')->group(function () {
    Route::get('/', NotificationChannels::class)->name('admin.notification-channels.index');
    Route::get('create', Create::class)->name('admin.notification-channels.create');
    Route::get('/{' . 'notificationChannel' . '}/edit', Edit::class)->name('admin.notification-channels.edit');
});