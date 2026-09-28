<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Reviews\Reviews;
use App\Livewire\Admin\Reviews\Create;
use App\Livewire\Admin\Reviews\Edit;
Route::prefix('reviews')->group(function () {
    Route::get('/', Reviews::class)->name('admin.reviews.index');
    Route::get('create', Create::class)->name('admin.reviews.create');
    Route::get('/{' . 'review' . '}/edit', Edit::class)->name('admin.reviews.edit');
});