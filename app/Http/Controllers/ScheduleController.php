<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Route;
use App\Models\Travel;
use App\Models\Location;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $request->input('q', Carbon::now()->format('Y-m')); // Default ke bulan ini (YYYY-MM)
        $schedules = Schedule::with(['route', 'travel'])
            ->where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('date', 'LIKE', '%' . $query . '%');
            })

            ->paginate(33)
            ->appends(['q' => $query]); // Tambahkan parameter query ke pagination URL

        $title = 'Daftar Jadwal';
        return view('schedules/index', compact('schedules', 'title', 'query'));
    }

    //nanti disini untuk api searching kursi yg available
    public function index2(Request $request)
    {
        try {
            $query = $request->input('q', Carbon::now()->format('Y-m')); // Default ke bulan ini (YYYY-MM)
            $schedules = Schedule::with(['route', 'travel'])
                ->where(function ($queryBuilder) use ($query) {
                    $queryBuilder->where('date', 'LIKE', '%' . $query . '%');
                })

                ->paginate(33);
            return new ResponseResource(true, "list schedule", $schedules, 200);
        } catch (\Exception $e) {
            return new ResponseResource(false, $e->getMessage(), [], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $routes = Route::with('fromLocation', 'toLocation')->get();
        $travels = Travel::select('id', 'name')->get();
        $formattedRoutes = $routes->map(function ($route) {
            return [
                'id' => $route->id,
                'from_location_id' => $route->from_location_id,
                'to_location_id' => $route->to_location_id,
                'from_location' => $route->fromLocation->name, // Ambil nama lokasi asal
                'to_location' => $route->toLocation->name,     // Ambil nama lokasi tujuan
            ];
        });
        return view('schedules/create', ['title' => 'tambah jadwal', 'routes' => $formattedRoutes, 'travels' => $travels]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date', // Memastikan input adalah format tanggal yang valid
            'time' => 'required|date_format:H:i', // Memastikan input adalah format waktu (jam:menit)
            'route_id' => 'required|integer|exists:routes,id',
            'travel_id' => 'required|integer|exists:travels,id',
        ]);

        if (Schedule::where('date', $request->date)
            ->where('time', $request->time)
            ->where('route_id', $request->route_id)
            ->where('travel_id', $request->travel_id)
            ->exists()
        ) {
            return redirect()->back()
                ->withErrors(['unique_combination' => 'Jadwal sudah ada.'])
                ->withInput();
        }


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Schedule::create([
            'date' => $request->date,
            'time' => $request->time,
            'route_id' => $request->route_id,
            'travel_id' => $request->travel_id,
        ]);

        // Redirect to the users index page with a success message
        return redirect()->route('schedules.index')->with('success', 'Schedule created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $schedule = Schedule::with(['route', 'travel'])->find($id);

        if (!$schedule) {
            return redirect()->route('schedules.index')->with('error', 'Schedule not found.');
        }
        $title = 'Detail Jadwal';
        return view('schedules.show', compact(['schedule', 'title']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $routes = Route::with('fromLocation', 'toLocation')->get();
        $travels = Travel::with(['seats'])->get();
        $formattedRoutes = $routes->map(function ($route) {
            return [
                'id' => $route->id,
                'from_location_id' => $route->from_location_id,
                'to_location_id' => $route->to_location_id,
                'from_location' => $route->fromLocation->name, // Ambil nama lokasi asal
                'to_location' => $route->toLocation->name,     // Ambil nama lokasi tujuan
            ];
        });

        $schedule = Schedule::findOrFail($id);

        return view('schedules.edit', [
            'title' => 'Edit Jadwal',
            'schedule' => $schedule,
            'routes' => $formattedRoutes,
            'travels' => $travels,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Schedule $schedule)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date', // Memastikan input adalah format tanggal yang valid
            'time' => 'required|date_format:H:i', // Memastikan input adalah
            'route_id' => 'required|integer|exists:routes,id',
            'travel_id' => 'required|integer|exists:travels,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        if (Schedule::where('date', $request->date)
            ->where('time', $request->time)
            ->where('route_id', $request->route_id)
            ->where('travel_id', $request->travel_id)
            ->where('id', '!=', $schedule->id) // Kecualikan data dengan ID yang sedang diedit
            ->exists()
        ) {
            return redirect()->back()
                ->withErrors(['unique_combination' => 'Jadwal sudah ada.'])
                ->withInput();
        }
        DB::beginTransaction();
        try {
            // Update data jadwal
            $schedule->update([
                'date' => $request->date,
                'time' => $request->time,
                'route_id' => $request->route_id,
                'travel_id' => $request->travel_id,
            ]);

            // Update status kursi jika ada
            if ($request->has('seats')) {
                foreach ($request->seats as $seatNumber => $status) {
                    $schedule->travel->seats()
                        ->where('seat_number', $seatNumber)
                        ->update(['status' => $status]);
                }
            }
            DB::commit();
            return redirect()->route('schedules.index')->with('success', 'Schedule updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        try {
            $schedule->delete();
            return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus jadwal: ' . $e->getMessage());
        }
    }

    public function view_generate_schedule()
    {
        $routes = Route::with('fromLocation', 'toLocation')->get();
        $travels = Travel::select('id', 'name')->get();
        $formattedRoutes = $routes->map(function ($route) {
            return [
                'id' => $route->id,
                'from_location_id' => $route->from_location_id,
                'to_location_id' => $route->to_location_id,
                'from_location' => $route->fromLocation->name, // Ambil nama lokasi asal
                'to_location' => $route->toLocation->name,     // Ambil nama lokasi tujuan
            ];
        });

        return view('schedules/generateByMonth', [
            'title' => 'Generate Schedule',
            'routes' => $formattedRoutes,
            'travels' => $travels,
        ]);
    }

    public function generate_schedule_by_month(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'required|date_format:Y-m', // Validasi format bulan
            'route_id' => 'required|integer|exists:routes,id',
            'travel_id' => 'required|integer|exists:travels,id',
            'time' => 'required|array', // Pastikan waktu adalah array
            'time.*' => 'required|date_format:H:i', // Validasi setiap elemen waktu
        ], [
            'month.required' => 'Bulan wajib diisi.',
            'month.date_format' => 'Format bulan harus YYYY-MM.',
            'route_id.required' => 'Rute wajib dipilih.',
            'route_id.exists' => 'Rute yang dipilih tidak valid.',
            'travel_id.required' => 'Travel wajib dipilih.',
            'travel_id.exists' => 'Travel yang dipilih tidak valid.',
            'time.required' => 'Waktu wajib diisi.',
            'time.*.date_format' => 'Format waktu harus HH:mm.',
        ]);


        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Ambil bulan dan tahun dari input
        $month = $request->month; // Format: YYYY-MM
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth(); // Tanggal awal bulan
        $daysInMonth = $startDate->daysInMonth; // Jumlah hari dalam bulan

        try {
            DB::beginTransaction();

            // Generate jadwal untuk setiap hari dalam bulan tersebut
            $schedules = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = $startDate->copy()->day($day); // Set tanggal berdasarkan hari
                foreach ($request->time as $time) {
                    // Cek apakah kombinasi sudah ada
                    if (Schedule::where('date', $date->format('Y-m-d'))
                        ->where('time', $time)
                        ->where('route_id', $request->route_id)
                        ->where('travel_id', $request->travel_id)
                        ->exists()
                    ) {
                        // Jika kombinasi sudah ada, lewati iterasi ini
                        continue;
                    }

                    // Tambahkan ke array $schedules jika kombinasi belum ada
                    $schedules[] = [
                        'date' => $date->format('Y-m-d'), // Format tanggal
                        'time' => $time, // Waktu dari array
                        'route_id' => $request->route_id,
                        'travel_id' => $request->travel_id,
                        'created_at' => Carbon::now('Asia/Jakarta'),
                        'updated_at' => Carbon::now('Asia/Jakarta'),
                    ];
                }
            }

            // Simpan semua jadwal ke database
            if (!empty($schedules)) {
                Schedule::insert($schedules);
            }
            DB::commit();

            return redirect()->route('schedules.index')->with('success', 'Jadwal untuk bulan ' . $month . ' berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function search_schedule(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->query(), [
            'from' => 'required|string', // Nama lokasi asal
            'to' => 'required|string',   // Nama lokasi tujuan
            'date' => 'required|date_format:Y-m-d', // Format tanggal
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        // Cari route berdasarkan nama lokasi asal dan tujuan
        $route = Route::whereHas('fromLocation', function ($query) use ($request) {
                $query->where('name', $request->query('from'));
            })
            ->whereHas('toLocation', function ($query) use ($request) {
                $query->where('name', $request->query('to'));
            })
            ->first();
    
        if (!$route) {
            return new ResponseResource(false, "Route not found for the given locations.", [], 404);
        }
    
        $date = $request->query('date');
    
        // Query jadwal berdasarkan route dan tanggal
        $schedules = Schedule::with(['route', 'travel'])
            ->where('route_id', $route->id)
            ->where('date', $date)
            ->get();


            $formattedSchedules = $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'date' => $schedule->date,
                    'time' => $schedule->time,
                    'from' => $schedule->route->fromLocation->name,
                    'to' => $schedule->route->toLocation->name,
                    'from_location_id' =>$schedule->route->fromLocation->id,
                    'to_location_id' =>$schedule->route->toLocation->id,
                    'travel_id' => $schedule->travel->id,
                    'travel_name' => $schedule->travel->name,
                ];
            });
    
    
        // Jika tidak ada jadwal ditemukan
        if ($schedules->isEmpty()) {
            return new ResponseResource(false, "No schedules found ", [], 404);
        }
    
        return new ResponseResource(true, "List of schedules", $formattedSchedules, 200);
    }
}
