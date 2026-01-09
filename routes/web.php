<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('dashboard'); // ito ang entry ng SPA mo
});

Route::get('/test', function () {
    return 'OK';
});

// SPA catch-all
Route::view('/{any}', 'dashboard')
    ->where('any', '.*');

Route::middleware('auth')->group(function () {
    Route::post('/users/avatar', [ProfileController::class, 'uploadAvatar'])
        ->name('users.avatar');
});

