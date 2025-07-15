<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public authentication routes
Route::prefix('auth/email')->group(function () {
    Route::post('/send-magic-link', [AuthController::class, 'sendMagicLink']);
    Route::post('/verify-magic-link', [AuthController::class, 'verifyMagicLink']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/email/send-verification', [AuthController::class, 'sendEmailVerification']);
    
    // User management
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    Route::delete('/user', [UserController::class, 'destroy']);
});
