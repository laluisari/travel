<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ScheduleController;

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

//Travel
Route::get('travels', [TravelController::class, 'index2']);
Route::get('travels/{id}', [TravelController::class, 'show2']);

//schedule
Route::get('schedules', [ScheduleController::class, 'index2']);
Route::get('schedules/{id}', [ScheduleController::class, 'show2']);

Route::get('view_generate_schedule2', [ScheduleController::class, 'view_generate_schedule2']);

