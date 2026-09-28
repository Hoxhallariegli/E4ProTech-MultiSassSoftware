<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\MessageLogs\MessageLogs;
use App\Livewire\Admin\MessageLogs\Create;
use App\Livewire\Admin\MessageLogs\Edit;
Route::prefix('message-logs')->group(function () {
    Route::get('/', MessageLogs::class)->name('admin.message-logs.index');
    Route::get('create', Create::class)->name('admin.message-logs.create');
    Route::get('/{' . 'messageLog' . '}/edit', Edit::class)->name('admin.message-logs.edit');
});