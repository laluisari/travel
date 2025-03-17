<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = Location::select('id', 'name')->paginate(33);
        $title = 'Daftar Lokasi';
        return view('locations/index', compact('locations', 'title'));
    }

    public function index2()
    {
        try {
            $locations = Location::select('id', 'name')->paginate(33);
            return new ResponseResource(true, "list location", $locations, 200);
        } catch (\Exception $e) {
            return new ResponseResource(false, $e->getMessage(), [], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('locations/create', ['title' => 'tambah lokasi']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:locations',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput(); 
        }

        Location::create([
            'name' => $request->name,
        ]);

        // Redirect to the users index page with a success message
        return redirect()->route('locations.index')->with('success', 'Seat created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $location = Location::find($id);
            return new ResponseResource(true, "detail location", $location, 200);
        } catch (\Exception $e) {
            return new ResponseResource(false, $e->getMessage(), [], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location)
    {
        return view('locations.edit', ['location' => $location]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:locations,name,' . $location->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location = Location::findOrFail($location->id);
        $location->name = $request->name;
        $location->save();
        return redirect()->route('locations.index')->with('success', 'Seat created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        try {
            $location->delete();
            return redirect()->route('locations.index')->with('success', 'Location deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('locations.index')->with('error', 'Failed to delete Location. Please try again.');
        }
    }
}
