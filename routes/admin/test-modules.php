<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\TestModules\TestModules;
use App\Livewire\Admin\TestModules\Create;
use App\Livewire\Admin\TestModules\Edit;
Route::prefix('test-modules')->group(function () {
    Route::get('/', TestModules::class)->name('admin.test-modules.index');
    Route::get('create', Create::class)->name('admin.test-modules.create');
    Route::get('/{' . 'testModule' . '}/edit', Edit::class)->name('admin.test-modules.edit');
});