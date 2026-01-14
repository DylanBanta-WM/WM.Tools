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

// GAM API endpoint for checking email existence
Route::post('/api/gam/check-email', [GamController::class, 'checkEmail'])->name('api.gam.check-email');
