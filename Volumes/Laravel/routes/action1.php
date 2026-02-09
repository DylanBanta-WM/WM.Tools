<?php

use App\Http\Controllers\Action1Controller;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Action1 API Routes
|--------------------------------------------------------------------------
|
| Routes for interacting with the Action1 RMM API
| All routes require authentication and admin privileges
|
*/

Route::middleware(['auth', 'admin'])->group(function () {
    // Action1 API interface
    Route::get('/API/Action1', [Action1Controller::class, 'index'])->name('action1.index');

    // Action1 API endpoints
    Route::prefix('api/action1')->name('api.action1.')->group(function () {
    // Authentication
    Route::post('/auth', [Action1Controller::class, 'authenticate'])->name('auth');

    // Organizations
    Route::post('/organizations', [Action1Controller::class, 'listOrganizations'])->name('organizations');

    // Reports
    Route::post('/reports', [Action1Controller::class, 'listReports'])->name('reports');
    Route::post('/reports/category', [Action1Controller::class, 'getReportsByCategory'])->name('reports.category');
    Route::post('/reports/data', [Action1Controller::class, 'getReportData'])->name('reports.data');
    Route::post('/reports/requery', [Action1Controller::class, 'requeryReport'])->name('reports.requery');

    // Endpoints
    Route::post('/endpoints', [Action1Controller::class, 'listEndpoints'])->name('endpoints');
    Route::post('/endpoints/status', [Action1Controller::class, 'getEndpointStatus'])->name('endpoints.status');

    // Search
    Route::post('/search', [Action1Controller::class, 'search'])->name('search');

    // Generic request endpoint
    Route::post('/request', [Action1Controller::class, 'request'])->name('request');
    });
});
