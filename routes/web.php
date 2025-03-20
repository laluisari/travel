<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\TravelController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('home/index', ['title' => 'Home Page']);
});

Route::resource('users', UserController::class)->except('show');
Route::resource('seats', SeatController::class)->except('show');
Route::resource('locations', LocationController::class)->except('show');
Route::resource('routes', RouteController::class)->except('show');
Route::resource('travels', TravelController::class);
//schedules
Route::resource('schedules', ScheduleController::class);
Route::get('/view_generate_schedule', [ScheduleController::class, 'view_generate_schedule'])->name('view_generate_schedule');
Route::post('generate_schedule_by_month', [ScheduleController::class, 'generate_schedule_by_month'])->name('generate_schedule_by_month');
