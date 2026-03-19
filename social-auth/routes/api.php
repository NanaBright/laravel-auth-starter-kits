<?php

use App\Http\Controllers\Auth\SocialController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [SocialController::class, 'user']);
    Route::post('/auth/logout', [SocialController::class, 'logout']);
    
    // Connected accounts management
    Route::get('/user/connected-accounts', [SocialController::class, 'connectedAccounts']);
    Route::delete('/user/connected-accounts/{id}', [SocialController::class, 'disconnect']);
});
