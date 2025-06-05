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
    Route::get('search_schedule', [ScheduleController::class, 'search_schedule']);
 

    //logout
    Route::post('/logout', [AuthController::class, 'customerLogout']);

    //booking
    Route::post('bookings', [BookingController::class, 'store']);
    
    //payment
    Route::get('history_payment_customer', [PaymentController::class, 'history_payment_customer']);
    
});
Route::post('/midtrans/webhook', [BookingController::class, 'handleWebhook']);

Route::get('bookings', [BookingController::class, 'index2']);

Route::post('/login', [AuthController::class, 'customerLogin']);
Route::post('/register', [AuthController::class, 'customerRegister']);

