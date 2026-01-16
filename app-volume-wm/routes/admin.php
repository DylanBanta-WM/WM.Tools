<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('administration')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/stats', [AdminController::class, 'stats'])->name('admin.stats');
    Route::post('/run-job', [AdminController::class, 'runJob'])->name('admin.runJob');
    Route::get('/job-status', [AdminController::class, 'jobStatus'])->name('admin.jobStatus');

    // Usage data management
    Route::get('/usage', [AdminController::class, 'usage'])->name('admin.usage');
    Route::get('/usage/data', [AdminController::class, 'usageData'])->name('admin.usageData');
    Route::post('/usage', [AdminController::class, 'createUsageRecord'])->name('admin.usage.create');
    Route::put('/usage/{id}', [AdminController::class, 'updateUsageRecord'])->name('admin.usage.update');
    Route::delete('/usage/{id}', [AdminController::class, 'deleteUsageRecord'])->name('admin.usage.delete');
});
