<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use Illuminate\Support\Facades\Validator;

class SeatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $seats = Seat::select('id', 'seat_number', 'price')->paginate(33);
        $title = 'Daftar Kursi';
        return view('seats/index', compact('seats', 'title'));
    }
    public function index2()
    {
        try {
            $seats = Seat::select('id', 'seat_number', 'price')->paginate(33);
            return new ResponseResource(true, "list kursi", $seats, 200);
        } catch (\Exception $e) {
            return new ResponseResource(false, $e->getMessage(), [], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('seats/create', ['title' => 'tambah kursi']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'seat_number' => 'required|string|unique:seats',
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Seat::create([
            'seat_number' => $request->seat_number,
            'price' => $request->price,

        ]);

        // Redirect to the users index page with a success message
        return redirect()->route('seats.index')->with('success', 'Seat created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $seat = Seat::find($id);
        if (!$seat) {
            return new ResponseResource(false, "seat not found", [], 404);
        }
        return new ResponseResource(true, "detail seat", $seat, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Seat $seat)
    {
        return view('seats.edit', ['seat' => $seat]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Seat $seat)
    {
        $validator = Validator::make($request->all(), [
            'seat_number' => 'required|string|unique:seats,seat_number,' . $seat->id,
            'price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $seat = Seat::findOrFail($seat->id);
        $seat->seat_number = $request->seat_number;
        $seat->price = $request->price;
        $seat->save();
        return redirect()->route('seats.index')->with('success', 'Seat created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Seat $seat)
    {
        try {
            $seat->delete();
            return redirect()->route('seats.index')->with('success', 'Seat deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('seats.index')->with('error', 'Failed to delete seat. Please try again.');
        }
    }
}
