<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/magic-link/verify', function () {
    return view('app');
})->name('magic-link.verify');

Route::get('/dashboard', function () {
    return view('app');
})->middleware('auth');

// Catch-all route for SPA
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
