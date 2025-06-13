<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\Travel;
use App\Models\TravelSeat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\TravelResource;
use App\Http\Resources\ResponseResource;
use Illuminate\Support\Facades\Validator;

class TravelController extends Controller
{

    public function index()
    {
        $travels = Travel::select('id', 'name', 'type')->paginate(33);
        $title = 'Daftar Travel';
        return view('travels/index', compact('travels', 'title'));
    }

    public function index2()
    {
        try {
            $travels = Travel::select('id', 'name', 'type')->paginate(33); 
            return new ResponseResource(true, "list travels", $travels, 200);
        } catch (\Exception $e) {
            return new ResponseResource(false, $e->getMessage(), [], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $seats = Seat::all();
        return view('travels/create', ['title' => 'tambah travel', 'seats' => $seats]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:travels',
            'type' => 'required',
            'seat_ids' => 'required|array',
            'seat_ids.*' => 'required|exists:seats,id',
        ],
        [
            'name.unique' => 'Nama travel sudah digunakan!',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        DB::beginTransaction();
        try {
            // Buat travel baru
            $travel = Travel::create([
                'name' => $request->name,
                'type' => $request->type,
            ]);
    
            // Looping untuk menyimpan data ke tabel travel_seats
            foreach ($request->seat_ids as $seat_id) {
                TravelSeat::create([
                    'travel_id' => $travel->id,
                    'seat_id' => $seat_id,
                    'status' => 'available',
                    'schedule_id' => null
                ]);
            }
    
            DB::commit();
    
            return redirect()->route('travels.index')->with('success', 'Travel berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $travel = Travel::findOrFail($id);
            $title = 'Detail Travel';
            return view('travels.show', ['travel' => $travel, 'title' => $title]);
        } catch (\Exception $e) {
            return new ResponseResource(false, $e->getMessage(), [], 500);
        }
    }
    public function show2($id)
    {
        try {
            $travel = Travel::findOrFail($id);
            return new ResponseResource(true, "detail travel", new TravelResource($travel), 200);
        } catch (\Exception $e) {
            return new ResponseResource(false, $e->getMessage(), [], 500);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Travel $travel)
    {
        $seats = Seat::all();
        return view('travels.edit', ['travel' => $travel,  'seats' => $seats]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Travel $travel)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'type' => 'required',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        DB::beginTransaction();
    
        try {
            // Update data travel
            $travel->update([
                'name' => $request->name,
                'type' => $request->type,
            ]);
    
            // Update data di tabel travel_seats
            // Sinkronisasi seat_ids dengan tabel pivot travel_seats
            // $travel->seats()->sync($request->seat_ids);
    
            DB::commit();
    
            return redirect()->route('travels.index')->with('success', 'Travel berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Travel $travel)
    {
        try{
            $travel->delete();
            return redirect()->route('travels.index')->with('success', 'Travel deleted successfully');
        }catch (\Exception $e) {
            return redirect()->route('travels.index')->with('error', 'Travel deleted successfully');
        }
    }
}





