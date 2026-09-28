<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\WorkingHours\WorkingHours;
use App\Livewire\Admin\WorkingHours\Create;
use App\Livewire\Admin\WorkingHours\Edit;
Route::prefix('working-hours')->group(function () {
    Route::get('/', WorkingHours::class)->name('admin.working-hours.index');
    Route::get('create', Create::class)->name('admin.working-hours.create');
    Route::get('/{' . 'workingHour' . '}/edit', Edit::class)->name('admin.working-hours.edit');
});