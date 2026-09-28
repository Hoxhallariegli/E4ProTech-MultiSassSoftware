<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\MessageQueues\MessageQueues;
use App\Livewire\Admin\MessageQueues\Create;
use App\Livewire\Admin\MessageQueues\Edit;
Route::prefix('message-queues')->group(function () {
    Route::get('/', MessageQueues::class)->name('admin.message-queues.index');
    Route::get('create', Create::class)->name('admin.message-queues.create');
    Route::get('/{' . 'messageQueue' . '}/edit', Edit::class)->name('admin.message-queues.edit');
});