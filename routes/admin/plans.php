<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Plans\Plans;
use App\Livewire\Admin\Plans\Create;
use App\Livewire\Admin\Plans\Edit;
Route::prefix('plans')->group(function () {
    Route::get('/', Plans::class)->name('admin.plans.index');
    Route::get('create', Create::class)->name('admin.plans.create');
    Route::get('/{' . 'plan' . '}/edit', Edit::class)->name('admin.plans.edit');
});