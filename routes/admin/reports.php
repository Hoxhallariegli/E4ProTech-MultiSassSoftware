<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Reports\Reports;

Route::prefix('reports')->group(function () {
    Route::get('/', Reports::class)->name('admin.reports.index');
});
