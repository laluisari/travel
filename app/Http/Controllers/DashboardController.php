<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Travel;
use App\Models\Customer;
use App\Models\Schedule;
use App\Models\Dashboard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Dashboard $dashboard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dashboard $dashboard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dashboard $dashboard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dashboard $dashboard)
    {
        //
    }

    public function refreshDataDashboard()
    {
        $customer = Customer::count();
        $travel = Travel::count();
        $schedules = Schedule::whereDate('date', Carbon::today())
        ->with(['travel', 'route'])->get();
        $schedules = [
            'total' => $schedules->count(),
            'schedules' => $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'travel' => $schedule->travel->name,
                    'route_from' => $schedule->route->fromLocation->name,
                    'route_to' => $schedule->route->toLocation->name,
                    'date' => $schedule->date,
                    'time' => $schedule->time,
                ];
            }),
        ];
        return new ResponseResource(true, 'success', [
            'customer' => $customer,
            'travel' => $travel,
            'schedules' => $schedules,
        ], 200);
    }
}
