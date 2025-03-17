<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\SeatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('home/index', ['title' => 'Home Page']);
});

Route::get('/bus', function () {
    return view('bus/index', ['title' => 'Bus Page']);
});

Route::resource('users', UserController::class)->except('show');
Route::resource('seats', SeatController::class)->except('show');
Route::resource('locations', LocationController::class)->except('show');
Route::resource('routes', RouteController::class)->except('show');
