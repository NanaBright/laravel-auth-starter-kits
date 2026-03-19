<?php

use App\Http\Controllers\Auth\SocialController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// OAuth routes
Route::prefix('auth')->group(function () {
    Route::get('/{provider}', [SocialController::class, 'redirect'])
        ->name('social.redirect');
    
    Route::get('/{provider}/callback', [SocialController::class, 'callback'])
        ->name('social.callback');
});

// SPA catch-all route
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
