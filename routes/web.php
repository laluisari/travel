<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('home/index', ['title' => 'Home Page']);
});

Route::get('/bus', function () {
    return view('bus/index', ['title' => 'Bus Page']);
});

Route::resource('users', UserController::class)->except('show');