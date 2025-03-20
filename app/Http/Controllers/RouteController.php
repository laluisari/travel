<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $routes = Route::with(['fromLocation', 'toLocation'])->paginate(33);
        $title = 'Daftar Route';
        return view('routes/index', compact('routes', 'title'));
    }

    public function index2()
    {
        try {
            $routes = Route::with(['fromLocation', 'toLocation'])->paginate(33);
            // Gunakan RouteResource untuk memformat data
            return new ResponseResource(true, "list routes", \App\Http\Resources\RouteResource::collection($routes), 200);
        } catch (\Exception $e) {
            return new ResponseResource(false, $e->getMessage(), [], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $locations = Location::all();
        return view('routes/create', ['title' => 'tambah route', 'locations' => $locations]);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_location_id' => 'required',
            'to_location_id' => 'required',
        ]);

        // Validasi kombinasi unik
        if (Route::where('from_location_id', $request->from_location_id)
            ->where('to_location_id', $request->to_location_id)
            ->exists()
        ) {
            return redirect()->back()
                ->withErrors(['unique_combination' => 'Rute dengan titik berangkat dan titik tujuan sudah ada.'])
                ->withInput();
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Route::create([
            'from_location_id' => $request->from_location_id,
            'to_location_id' => $request->to_location_id,
        ]);

        // Redirect to the users index page with a success message
        return redirect()->route('routes.index')->with('success', 'Routes created successfully.');
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $route = Route::find($id);
            return new ResponseResource(true, "detail route", $route, 200);
        } catch (\Exception $e) {
            return new ResponseResource(false, $e->getMessage(), [], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Route $route)
    {
        $locations = Location::all();
        return view('routes.edit', ['route' => $route,  'locations' => $locations]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Route $route)
    {
        $validator = Validator::make($request->all(), [
            'from_location_id' => 'required',
            'to_location_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $route = Route::findOrFail($route->id);
        $route->from_location_id = $request->from_location_id;
        $route->to_location_id = $request->to_location_id;
        $route->save();
        return redirect()->route('routes.index')->with('success', 'Seat created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Route $route)
    {
        try {
            $route->delete();
            return redirect()->route('routes.index')->with('success', 'Route deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('routes.index')->with('error', 'Failed to delete Route. Please try again.');
        }
    }
}
