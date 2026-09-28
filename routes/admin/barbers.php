<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Barbers\Barbers;
use App\Livewire\Admin\Barbers\Create;
use App\Livewire\Admin\Barbers\Edit;

Route::prefix('staff')->group(function () {
    Route::get('/', Barbers::class)->name('admin.barbers.index');
    Route::get('create', Create::class)->name('admin.barbers.create');
    Route::get('/{' . 'barber' . '}/edit', Edit::class)->name('admin.barbers.edit');
});
