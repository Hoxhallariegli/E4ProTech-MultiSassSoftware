<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\BarberShops\BarberShops;
use App\Livewire\Admin\BarberShops\Create;
use App\Livewire\Admin\BarberShops\Edit;

Route::prefix('shops')->group(function () {
    Route::get('/', BarberShops::class)->name('admin.barber-shops.index');
    Route::get('create', Create::class)->name('admin.barber-shops.create');
    Route::get('my-salon', Edit::class)->name('admin.my-salon');
    Route::get('/{' . 'barberShop' . '}/edit', Edit::class)->name('admin.barber-shops.edit');
});
