<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\LocationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//users
Route::get('users/{id}', [UserController::class, 'show']);

//seat
Route::get('seats', [SeatController::class, 'index2']);
Route::get('seats/{id}', [SeatController::class, 'show']);


//location
Route::get('locations', [LocationController::class, 'index2']);
Route::get('locations/{id}', [LocationController::class, 'show']);


//Route
Route::get('routes', [RouteController::class, 'index2']);