<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\BulkCancellations\BulkCancellations;
use App\Livewire\Admin\BulkCancellations\Create;
use App\Livewire\Admin\BulkCancellations\Edit;
Route::prefix('bulk-cancellations')->group(function () {
    Route::get('/', BulkCancellations::class)->name('admin.bulk-cancellations.index');
    Route::get('create', Create::class)->name('admin.bulk-cancellations.create');
    Route::get('/{' . 'bulkCancellation' . '}/edit', Edit::class)->name('admin.bulk-cancellations.edit');
});