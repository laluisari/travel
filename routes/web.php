<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ScheduleController;


Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        $dashboard = new DashboardController;
        $dataDashboard = $dashboard->refreshDataDashboard();
        return view('home.index', [
            'title' => 'Dashboard Page',
            'dataDashboard' => $dataDashboard,
        ]);
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

    Route::resource('bookings', BookingController::class);
    Route::get('search_payment', [BookingController::class, 'searchSchedule'])->name('search_payment');
    Route::get('show_manual_pay/{id}', [BookingController::class, 'view_manual_pay'])->name('manual_pay');
    Route::post('create_manual_pay', [BookingController::class, 'manual_pay'])->name('create_manual_pay');
});


Route::get('login', [AuthController::class, 'adminLoginView'])->name('login');
Route::post('admin/login', [AuthController::class, 'adminLogin'])->name('admin.login');
Route::post('/admin/logout', [AuthController::class, 'adminLogout'])->name('admin.logout');
