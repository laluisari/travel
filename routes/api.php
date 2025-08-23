<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ScheduleController;

Route::fallback(function () {
    return response()->json(['status' => false, 'message' => 'Not Found!'], 404);
});

Route::middleware('auth.api')->group(function () {
    //users
    Route::get('users/{id}', [UserController::class, 'show']);

    //logout
    Route::post('/logout', [AuthController::class, 'customerLogout']);

    //booking
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings', [BookingController::class, 'index2']);

    Route::post('create-snap-token', [BookingController::class, 'createSnapToken']);

    //payment
    Route::get('history_payment_customer', [PaymentController::class, 'history_payment_customer']);


});


//seat
Route::get('seats', [SeatController::class, 'index2']);
Route::get('seats/{id}', [SeatController::class, 'show']);

//Route
Route::get('routes', [RouteController::class, 'index2']);

//Travel
Route::get('travels', [TravelController::class, 'index2']);
Route::get('travels/{id}', [TravelController::class, 'show2']);

//location
Route::get('locations', [LocationController::class, 'index2']);
Route::get('locations/{id}', [LocationController::class, 'show']);

//scheduler
Route::get('schedules', [ScheduleController::class, 'index2']);
Route::get('schedules/{id}', [ScheduleController::class, 'show2']);
Route::get('search_schedule', [ScheduleController::class, 'search_schedule']);

Route::post('/midtrans/webhook', [BookingController::class, 'handleWebhook']);


Route::post('/login', [AuthController::class, 'customerLogin']);
Route::post('/register', [AuthController::class, 'customerRegister']);
