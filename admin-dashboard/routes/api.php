<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\ActivityLogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');
});

// Protected admin routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
    });

    // Admin routes
    Route::prefix('admin')->group(function () {
        // Statistics
        Route::get('/stats', [StatsController::class, 'index']);
        Route::get('/stats/registrations', [StatsController::class, 'registrations']);
        Route::get('/stats/activity', [StatsController::class, 'activity']);

        // Users
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/export', [UserController::class, 'export']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::post('/users/bulk-delete', [UserController::class, 'bulkDelete']);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::post('/users/{user}/resend-verification', [UserController::class, 'resendVerification']);
        Route::post('/users/{user}/mark-verified', [UserController::class, 'markVerified']);

        // Activity logs
        Route::get('/logs', [ActivityLogController::class, 'index']);
        Route::get('/logs/actions', [ActivityLogController::class, 'actions']);
        Route::get('/logs/user/{userId}', [ActivityLogController::class, 'userLogs']);
    });
});
