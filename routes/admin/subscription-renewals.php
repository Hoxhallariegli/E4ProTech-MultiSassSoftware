<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\SubscriptionRenewals\SubscriptionRenewals;
use App\Livewire\Admin\SubscriptionRenewals\Create;
use App\Livewire\Admin\SubscriptionRenewals\Edit;
Route::prefix('subscription-renewals')->group(function () {
    Route::get('/', SubscriptionRenewals::class)->name('admin.subscription-renewals.index');
    Route::get('create', Create::class)->name('admin.subscription-renewals.create');
    Route::get('/{' . 'subscriptionRenewal' . '}/edit', Edit::class)->name('admin.subscription-renewals.edit');
});