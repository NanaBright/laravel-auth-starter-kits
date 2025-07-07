<?php
use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return view('app'); // or whatever your main blade file is
})->where('any', '.*');