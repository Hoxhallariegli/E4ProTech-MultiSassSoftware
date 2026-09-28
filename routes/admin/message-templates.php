<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\MessageTemplates\MessageTemplates;
use App\Livewire\Admin\MessageTemplates\Create;
use App\Livewire\Admin\MessageTemplates\Edit;
Route::prefix('message-templates')->group(function () {
    Route::get('/', MessageTemplates::class)->name('admin.message-templates.index');
    Route::get('create', Create::class)->name('admin.message-templates.create');
    Route::get('/{' . 'messageTemplate' . '}/edit', Edit::class)->name('admin.message-templates.edit');
});