<?php
use App\Http\Controllers\GamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| GAM Student Email Creator Routes
|--------------------------------------------------------------------------
|
| Routes for creating unique student email addresses
|
*/

// New Student email creator interface (no auth required)
Route::get('/newstudent', [GamController::class, 'newStudent'])->name('gam.newStudent');

// Chromebook lookup interface
Route::get('/chromebook-lookup', [GamController::class, 'chromebookLookup'])->name('gam.chromebookLookup');

// GAM API endpoint for checking email existence
Route::post('/api/gam/check-email', [GamController::class, 'checkEmail'])->name('api.gam.check-email');

// Chromebook lookup API endpoints
Route::post('/api/gam/chromebook-by-serial', [GamController::class, 'chromebookBySerial'])->name('api.gam.chromebook-by-serial');
Route::post('/api/gam/chromebook-by-user', [GamController::class, 'chromebookByUser'])->name('api.gam.chromebook-by-user');
