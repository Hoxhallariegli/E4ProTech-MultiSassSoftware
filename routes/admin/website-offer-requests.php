<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\WebsiteOfferRequests\WebsiteOfferRequests;
use App\Livewire\Admin\WebsiteOfferRequests\Create;
use App\Livewire\Admin\WebsiteOfferRequests\Edit;

Route::prefix('website-offer-requests')->group(function () {
    Route::get('/', WebsiteOfferRequests::class)->name('admin.website-offer-requests.index');
    Route::get('create', Create::class)->name('admin.website-offer-requests.create');
    Route::get('/{' . 'websiteOfferRequest' . '}/edit', Edit::class)->name('admin.website-offer-requests.edit');
});
?>
