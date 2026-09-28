<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Subscriptions\Subscriptions;
use App\Livewire\Admin\Subscriptions\Create;
use App\Livewire\Admin\Subscriptions\Edit;
Route::prefix('subscriptions')->group(function () {
    Route::get('/', Subscriptions::class)->name('admin.subscriptions.index');
    Route::get('create', Create::class)->name('admin.subscriptions.create');
    Route::get('/{' . 'subscription' . '}/edit', Edit::class)->name('admin.subscriptions.edit');
});