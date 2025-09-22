<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Route;
use App\Models\Travel;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Schedule;
use App\Models\TravelSeat;
use Illuminate\Http\Request;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Booking::with('customer', 'schedule');

        $q = $request->input('q');
        if ($q) {
            $query->where(function ($query) use ($q) {
                $query->where('booking_code', 'like', "%$q%")
                    ->orWhereHas('customer', function ($subQuery) use ($q) {
                        $subQuery->where('name', 'like', "%$q%")
                            ->orWhere('email', 'like', "%$q%");
                    })
                    ->orWhereHas('schedule.route.fromLocation', function ($subQuery) use ($q) {
                        $subQuery->where('name', 'like', "%$q%");
                    })
                    ->orWhereHas('schedule.route.toLocation', function ($subQuery) use ($q) {
                        $subQuery->where('name', 'like', "%$q%");
                    });
            });
        }

        $bookings = $query->paginate(33);

        $title = 'Booking List';

        return view('bookings.index', compact(['bookings', 'title']));
    }

    public function index2()
    {
        $bookings = Booking::with('customer', 'schedule')->paginate(33);

        return new ResponseResource(
            true,
            "List of bookings",
            \App\Http\Resources\BookingResource::collection($bookings),
            200
        );
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
        $customerId = auth('customer_api')->id();

        // Validasi input 
        $validator = Validator::make($request->all(), [
            'total_seat' => 'required|integer',
            'total_price' => 'required|numeric',
            'schedule_id' => 'required|exists:schedules,id',
            'booking_seat_ids' => 'required|array', // Pastikan booking_seat_ids adalah array
            'booking_seat_ids.*' => 'required|exists:travel_seats,id', // Validasi setiap elemen array
            'bank' => 'required|in:bri,bca,mandiri,bni,permata' // Validasi bank yang didukung
        ], [
            'schedule_id.required' => 'Schedule ID is required',
            'schedule_id.exists' => 'Schedule ID does not exist',
            'booking_seat_ids.required' => 'Booking seats are required',
            'booking_seat_ids.array' => 'Booking seats must be an array',
            'booking_seat_ids.*.required' => 'Each travel seat ID is required',
            'booking_seat_ids.*.exists' => 'Travel seat ID does not exist',
        ]);

        if ($validator->fails()) {
            return new ResponseResource(false, 'Validation error', $validator->errors(), 422);
        }

        // Validasi double booking
        $existingBooking = Booking::where('customer_id', $customerId)
            ->where('schedule_id', $request->schedule_id)
            ->whereIn('status', ['pending', 'paid']) // Periksa status pending atau paid
            ->whereHas('bookingSeats', function ($query) use ($request) {
                $query->whereIn('travel_seat_id', $request->booking_seat_ids);
            })
            ->first();

        if ($existingBooking) {
            $payment = Payment::where('booking_id', $existingBooking->id)->first();
            return new ResponseResource(
                false,
                'You already have a pending booking for the selected seats.',
                ['bank' => $payment->bank, 'va' => $payment->virtual_account, 'status' => $payment->status],
                422
            );
        }

        DB::beginTransaction(); // Mulai transaksi database

        try {
            // Buat data booking
            $booking = Booking::create([
                'booking_code' => bookingCode($customerId),
                'customer_id' => $customerId,
                'schedule_id' => $request->schedule_id,
                'total_seat' => $request->total_seat,
                'total_price' => $request->total_price,
                'status' => 'pending'
            ]);

            foreach ($request->booking_seat_ids as $travelSeatId) {
                $booking->bookingSeats()->create([
                    'travel_seat_id' => $travelSeatId,
                ]);
                $travelSeat = TravelSeat::where('schedule_id', $booking->schedule_id)->where('id', $travelSeatId)->first();
                $travelSeat->update(['status' => 'booked']);
            }

            $midtransResponse = $this->midtransService->createTransaction($booking, $request->bank);

            if ($midtransResponse['success']) {
                $booking->update([
                    'midtrans_transaction_id' => $midtransResponse['transaction_id']
                ]);

                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'virtual_account' => $midtransResponse['va_numbers'][0]->va_number,
                    'bank' => $midtransResponse['va_numbers'][0]->bank,
                    'status' => 'pending'
                ]);

                DB::commit();

                return new ResponseResource(true, 'Booking created successfully', [
                    'booking_code' => $booking->booking_code,
                    'va_numbers' => $midtransResponse['va_numbers'],
                    'transaction_status' => $midtransResponse['transaction_status'],
                    'transaction_time' => $midtransResponse['transaction_time'],
                    'transaction_id' => $midtransResponse['transaction_id'],
                    'gross_amount' => $midtransResponse['gross_amount'],
                    'expiry_time' => $midtransResponse['expiry_time'],
                ], 201);
            } else {
                // Hapus booking jika transaksi gagal
                $booking->delete();
                DB::rollBack(); // Rollback transaksi jika Midtrans gagal

                return new ResponseResource(false, 'Failed to create booking', $midtransResponse, 500);
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi jika terjadi exception
            return new ResponseResource(false, 'Failed to create booking', $e->getMessage(), 500);
        }
    }


    public function searchSchedule(Request $request)
    {
        // Jika tidak ada parameter pencarian, tampilkan form kosong dengan daftar lokasi
        if (!$request->has('from') || !$request->has('to') || !$request->has('date')) {
            $locations = Location::all();
            return view('bookings.create_payment', [
                'title' => 'Cari Jadwal Perjalanan',
                'locations' => $locations,
            ]);
        }

        // Validasi input 
        $validator = Validator::make($request->query(), [
            'from' => 'required|string',
            'to' => 'required|string',
            'date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:' . now()->format('Y-m-d'), // Validasi minimal hari ini
            ],
        ], [
            'date.after_or_equal' => 'Tanggal pencarian harus hari ini atau setelahnya.',
        ]);

        // Ambil semua lokasi untuk form pencarian
        $locations = Location::all();

        if ($validator->fails()) {
            return view('bookings.create_payment', [
                'locations' => $locations,
                'schedules' => [], // Kosongkan hasil pencarian
            ])->withErrors($validator);
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
            return view('bookings.create_payment', [
                'locations' => $locations,
                'schedules' => [], // Kosongkan hasil pencarian
            ])->withErrors($validator);
        }

        $date = $request->query('date');

        // Query jadwal berdasarkan route dan tanggal
        $schedules = Schedule::with(['route.fromLocation', 'route.toLocation', 'travel'])
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
                'from_location_id' => $schedule->route->fromLocation->id,
                'to_location_id' => $schedule->route->toLocation->id,
                'travel_id' => $schedule->travel->id,
                'travel_name' => $schedule->travel->name,
            ];
        });

        // Tampilkan hasil pencarian
        return view('bookings.create_payment', [
            'title' => 'Hasil Pencarian Jadwal',
            'locations' => $locations,
            'schedules' => $formattedSchedules,
        ]);
    }

    // Tambahan method untuk mengecek status transaksi
    public function checkPaymentStatus($bookingCode)
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        $statusResponse = $this->midtransService->checkTransactionStatus($booking->booking_code);

        return response()->json($statusResponse);
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        $title = 'detail pesanan';
        return view('bookings.show', compact(['booking', 'title']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //
    }

    public function handleWebhook(Request $request)
    {
        try {
            $notification = new \Midtrans\Notification();

            $transactionStatus = $notification->transaction_status;
            $orderId = $notification->order_id;

            // Cari booking berdasarkan booking_code
            $booking = Booking::with('bookingSeats')->where('booking_code', $orderId)->first();

            if (!$booking) {
                Log::error("Booking not found for order_id: {$orderId}");
                return new ResponseResource(false, 'Booking not found', null, 404);
            }

            // Pindahkan update logika ke switch-case untuk lebih mudah dikontrol
            switch ($transactionStatus) {
                case 'settlement':
                    $booking->update(['status' => 'paid']);
                    Payment::where('booking_id', $booking->id)->update(['status' => 'paid']);
                    $this->updateSeatStatus($booking, 'paid');
                    break;
                case 'pending':
                    $booking->update(['status' => 'pending']);
                    Payment::where('booking_id', $booking->id)->update(['status' => 'pending']);
                    $this->updateSeatStatus($booking, 'booked');
                    break;
                case 'expire':
                case 'cancel':
                    $booking->update(['status' => 'failed']);
                    Payment::where('booking_id', $booking->id)->update(['status' => 'failed']);
                    $this->updateSeatStatus($booking, 'available');
                    break;
                default:
                    Log::warning("Unhandled transaction status: {$transactionStatus}");
                    break;
            }

            // Log webhook data
            Log::info('Webhook received', $request->all());

            return new ResponseResource(true, 'Webhook handled successfully', null, 200);
        } catch (\Exception $e) {
            Log::error('Error handling webhook: ' . $e->getMessage());
            return new ResponseResource(false, 'Failed to handle webhook', $e->getMessage(), 500);
        }
    }

    // Metode untuk update status kursi
    private function updateSeatStatus($booking, $newStatus)
    {
        TravelSeat::where('schedule_id', $booking->schedule_id)
            ->whereIn('id', $booking->bookingSeats->pluck('travel_seat_id'))
            ->update(['status' => $newStatus]);
    }

    public function view_manual_pay(Request $request, $id)
    {

        $title = 'Manual Payment';

        $schedule = Schedule::with(['route.fromLocation', 'route.toLocation', 'travel.travel_seats'])->find($id);

        if (!$schedule) {
            return redirect()->route('schedules.index')->with('error', 'Schedule not found.');
        }

        // Ambil data travel_seats yang sesuai dengan schedule_id ini
        $travelSeats = TravelSeat::with('seat')
            ->where('travel_id', $schedule->travel_id)
            ->where('schedule_id', $schedule->id) // Filter berdasarkan schedule_id
            ->get();

        $schduleData = [

            'id' => $schedule->id,
            'date' => $schedule->date,
            'time' => $schedule->time,
            'from' => $schedule->route->fromLocation->name,
            'to' => $schedule->route->toLocation->name,
            'travel_name' => $schedule->travel->name,
            'travel_seats' => $travelSeats->map(function ($travelSeat) {
                return [
                    'id' => $travelSeat->id,
                    'seat_number' => $travelSeat->seat->seat_number ?? null,
                    'status' => $travelSeat->status,
                    'price' => $travelSeat->seat->price ?? 0, // Pastikan ada harga kursi
                ];
            }),
        ];

        return view('bookings.manual_pay', [
            'title' => $title,
            'scheduleData' => $schduleData,
        ]);
    }

    public function manual_pay(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'schedule_id' => 'required|exists:schedules,id',
            'booking_seat_ids' => 'required|array|min:1',
            'booking_seat_ids.*' => 'exists:travel_seats,id',
            'total_seat' => 'required|numeric|min:1',
            'total_price' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,qris,transfer',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'no_wa' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Cek ketersediaan kursi yang dipilih
        $unavailableSeats = TravelSeat::whereIn('id', $request->booking_seat_ids)
            ->where('status', '!=', 'available')
            ->count();

        if ($unavailableSeats > 0) {
            return redirect()->back()->with('error', 'Beberapa kursi yang Anda pilih sudah tidak tersedia. Silakan pilih kursi lain.');
        }

        // Ambil user_id dari user yang login (jika ada)
        $userId = \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()->id : null;

        DB::beginTransaction();

        try {
            // Find or create customer
            $customer = $this->findOrCreateCustomer($request);

            // Tentukan status booking berdasarkan payment method
            $bookingStatus = $request->payment_method === 'cash' ? 'paid' : 'pending';

            // Buat booking
            $booking = Booking::create([
                'booking_code' => bookingCode($userId),
                'customer_id' => $customer->id,
                'schedule_id' => $request->schedule_id,
                'total_seat' => $request->total_seat,
                'total_price' => $request->total_price,
                'status' => $bookingStatus,
                'user_id' => $userId,
            ]);

            // Simpan detail kursi yang dipesan
            foreach ($request->booking_seat_ids as $travelSeatId) {
                $booking->bookingSeats()->create([
                    'travel_seat_id' => $travelSeatId,
                ]);

                // Update status kursi menjadi booked
                $travelSeat = TravelSeat::where('schedule_id', $booking->schedule_id)
                    ->where('id', $travelSeatId)
                    ->first();

                $travelSeat->update(['status' => 'booked']);
            }

            // Handle payment berdasarkan method
            $paymentResult = $this->handlePaymentMethod($request, $booking);

            DB::commit();

            // Return response berdasarkan payment method
            return $this->handlePaymentResponse($request->payment_method, $paymentResult, $booking);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Manual booking error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membuat pemesanan: ' . $e->getMessage());
        }
    }

    private function findOrCreateCustomer(Request $request)
    {
        $customer = null;

        if ($request->email) {
            $customer = Customer::where('email', $request->email)->first();
        }
        
        if (!$customer && $request->no_wa) {
            $customer = Customer::where('no_wa', $request->no_wa)->first();
        }

        if (!$customer) {
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email ?? null,
                'password' => null,
                'no_wa' => $request->no_wa ?? null,
                'type' => 'offline',
            ]);
        }

        return $customer;
    }

    private function handlePaymentMethod(Request $request, Booking $booking)
    {
        switch ($request->payment_method) {
            case 'cash':
                return $this->processCashPayment($booking);
            
            case 'qris':
                return $this->processQrisPayment($booking);
            
            case 'transfer':
                return $this->processTransferPayment($booking, $request->bank ?? 'bca');
            
            default:
                throw new \Exception('Invalid payment method');
        }
    }

    private function processCashPayment(Booking $booking)
    {
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'virtual_account' => null,
            'bank' => null,
            'payment_type' => 'cash',
            'status' => 'paid'
        ]);

        return [
            'success' => true,
            'type' => 'cash',
            'payment' => $payment
        ];
    }

    private function processQrisPayment(Booking $booking)
    {
        $qrisResponse = $this->midtransService->createQrisTransaction($booking);

        if ($qrisResponse['success']) {
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'virtual_account' => null,
                'bank' => null,
                'payment_type' => 'qris',
                'qr_code_url' => $qrisResponse['qr_code_url'],
                'qris_data' => json_encode($qrisResponse),
                'status' => 'pending',
                'transaction_time' => now(),
                'expiry_time' => now()->addMinutes(15),
                'amount' => $booking->total_price
            ]);

            // Log untuk debugging
            Log::info('QRIS Payment Created: ', [
                'booking_code' => $booking->booking_code,
                'transaction_id' => $qrisResponse['transaction_id'],
                'qr_code_url' => $qrisResponse['qr_code_url']
            ]);

            return [
                'success' => true,
                'type' => 'qris',
                'payment' => $payment,
                'qr_code_url' => $qrisResponse['qr_code_url'],
                'transaction_id' => $qrisResponse['transaction_id'],
                'full_response' => $qrisResponse
            ];
        }

        throw new \Exception('Failed to generate QRIS: ' . $qrisResponse['message']);
    }

    private function processTransferPayment(Booking $booking, $bank)
    {
        $transferResponse = $this->midtransService->createTransaction($booking, $bank);

        if ($transferResponse['success']) {
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'virtual_account' => $transferResponse['va_numbers'][0]->va_number,
                'bank' => $transferResponse['va_numbers'][0]->bank,
                'payment_type' => 'bank_transfer',
                'status' => 'pending',
                'transaction_time' => now(),
                'expiry_time' => now()->addHours(24),
                'amount' => $booking->total_price
            ]);

            return [
                'success' => true,
                'type' => 'transfer',
                'payment' => $payment,
                'va_numbers' => $transferResponse['va_numbers']
            ];
        }

        throw new \Exception('Failed to generate Virtual Account: ' . $transferResponse['message']);
    }

    private function handlePaymentResponse($paymentMethod, $paymentResult, Booking $booking)
    {
        switch ($paymentMethod) {
            case 'cash':
                return redirect()->route('bookings.show', $booking->id)
                    ->with('success', 'Pemesanan berhasil dibuat! Pembayaran cash telah diterima.');

            case 'qris':
                return view('bookings.payment_qris', [
                    'title' => 'Pembayaran QRIS',
                    'booking' => $booking,
                    'qr_code_url' => $paymentResult['qr_code_url'],
                    'transaction_id' => $paymentResult['transaction_id']
                ]);

            case 'transfer':
                return view('bookings.payment_transfer', [
                    'title' => 'Pembayaran Transfer',
                    'booking' => $booking,
                    'va_numbers' => $paymentResult['va_numbers']
                ]);

            default:
                throw new \Exception('Invalid payment response type');
        }
    }

    //payment v.2
    public function createSnapToken2(Request $request)
    {
        $customerId = auth('customer_api')->id();

        // Validasi input 
        $validator = Validator::make($request->all(), [
            'total_seat' => 'required|integer',
            'total_price' => 'required|numeric',
            'schedule_id' => 'required|exists:schedules,id',
            'booking_seat_ids' => 'required|array',
            'booking_seat_ids.*' => 'required|exists:travel_seats,id',
            'bank' => 'required|in:bri,bca,mandiri,bni,permata'
        ], [
            'schedule_id.required' => 'Schedule ID is required',
            'schedule_id.exists' => 'Schedule ID does not exist',
            'booking_seat_ids.required' => 'Booking seats are required',
            'booking_seat_ids.array' => 'Booking seats must be an array',
            'booking_seat_ids.*.required' => 'Each travel seat ID is required',
            'booking_seat_ids.*.exists' => 'Travel seat ID does not exist',
        ]);

        if ($validator->fails()) {
            return new ResponseResource(false, 'Validation error', $validator->errors(), 422);
        }

        // Validasi double booking
        $existingBooking = Booking::where('customer_id', $customerId)
            ->where('schedule_id', $request->schedule_id)
            ->whereIn('status', ['pending', 'paid'])
            ->whereHas('bookingSeats', function ($query) use ($request) {
                $query->whereIn('travel_seat_id', $request->booking_seat_ids);
            })
            ->first();

        if ($existingBooking) {
            $payment = Payment::where('booking_id', $existingBooking->id)->first();
            return new ResponseResource(
                false,
                'You already have a pending booking for the selected seats.',
                ['bank' => $payment->bank, 'va' => $payment->virtual_account, 'status' => $payment->status],
                422
            );
        }

        DB::beginTransaction(); // Mulai transaksi database

        try {
            // Buat data booking
            $booking = Booking::create([
                'booking_code' => bookingCode($customerId),
                'customer_id' => $customerId,
                'schedule_id' => $request->schedule_id,
                'total_seat' => $request->total_seat,
                'total_price' => $request->total_price,
                'status' => 'pending'
            ]);

            foreach ($request->booking_seat_ids as $travelSeatId) {
                $booking->bookingSeats()->create([
                    'travel_seat_id' => $travelSeatId,
                ]);
                $travelSeat = TravelSeat::where('schedule_id', $booking->schedule_id)->where('id', $travelSeatId)->first();
                $travelSeat->update(['status' => 'booked']);
            }

            $midtransResponse = $this->midtransService->createSnapToken($booking, $request->bank);

            if ($midtransResponse['success']) {
                $booking->update([
                    'midtrans_transaction_id' => $midtransResponse['transaction_id']
                ]);

                // Simpan data payment
                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'virtual_account' => $midtransResponse['va_numbers'][0]->va_number,
                    'bank' => $midtransResponse['va_numbers'][0]->bank,
                    'status' => 'pending'
                ]);

                DB::commit();

                return new ResponseResource(true, 'Snap token created successfully', [
                    'snap_token' => $midtransResponse['snap_token'], // Pastikan untuk menambahkan snap_token di sini
                    'booking_code' => $booking->booking_code,
                    'transaction_status' => $midtransResponse['transaction_status'],
                    'transaction_time' => $midtransResponse['transaction_time'],
                    'transaction_id' => $midtransResponse['transaction_id'],
                    'gross_amount' => $midtransResponse['gross_amount'],
                    'expiry_time' => $midtransResponse['expiry_time'],
                ], 201);
            } else {
                // Hapus booking jika transaksi gagal
                $booking->delete();
                DB::rollBack(); // Rollback transaksi jika Midtrans gagal

                return new ResponseResource(false, 'Failed to create snap token', $midtransResponse, 500);
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi jika terjadi exception
            return new ResponseResource(false, 'Failed to create snap token', $e->getMessage(), 500);
        }
    }

    //payment v.2 snap
    public function createSnapToken(Request $request)
    {
        // Ambil data yang diperlukan dari request
        $customerId = auth('customer_api')->id();

        // Validasi input 
        $validator = Validator::make($request->all(), [
            'total_seat' => 'required|integer',
            'schedule_id' => 'required|exists:schedules,id',
            'booking_seat_ids' => 'required|array',
            'booking_seat_ids.*' => 'required|exists:travel_seats,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Cek double booking kursi (pending/paid) untuk user dan schedule ini
        $existingBooking = Booking::where('customer_id', $customerId)
            ->where('schedule_id', $request->schedule_id)
            ->whereIn('status', ['pending', 'paid'])
            ->whereHas('bookingSeats', function ($query) use ($request) {
                $query->whereIn('travel_seat_id', $request->booking_seat_ids);
            })
            ->first();

        if ($existingBooking) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending/paid booking for one or more selected seats.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Buat data booking (total_price sementara 0)
            $booking = Booking::create([
                'booking_code' => bookingCode($customerId),
                'customer_id' => $customerId,
                'schedule_id' => $request->schedule_id,
                'total_seat' => $request->total_seat,
                'total_price' => 0,
                'status' => 'pending'
            ]);

            $total_price = 0;
            foreach ($request->booking_seat_ids as $travelSeatId) {
                $booking->bookingSeats()->create([
                    'travel_seat_id' => $travelSeatId,
                ]);
                $travelSeat = TravelSeat::where('schedule_id', $booking->schedule_id)->where('id', $travelSeatId)->first();
                if ($travelSeat && $travelSeat->seat && $travelSeat->seat->price) {
                    $total_price += $travelSeat->seat->price;
                }
                if ($travelSeat) {
                    $travelSeat->update(['status' => 'booked']);
                }
            }

            // Update total_price booking setelah semua kursi diproses
            $booking->update(['total_price' => $total_price]);

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'virtual_account' => $midtransResponse['va_numbers'][0]->va_number ?? null,
                'bank' => $midtransResponse['va_numbers'][0]->bank ?? null,
                'status' => 'pending',
                'payment_type' => $midtransResponse['payment_type'] ?? 'bank_transfer',
                'transaction_time' => $midtransResponse['transaction_time'] ?? now(),
                'expiry_time' => $midtransResponse['expiry_time'] ?? null,
                'amount' => $midtransResponse['gross_amount'] ?? $booking->total_price,
            ]);


            Log::info('Booking Created', [
                'booking_code' => $booking->booking_code,
                'total_price' => $booking->total_price,
                'total_seat' => $booking->total_seat,
            ]);

            // Panggil fungsi createSnapToken di MidtransService
            $response = $this->midtransService->createSnapToken($booking);
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to create snap token', 'error' => $e->getMessage()], 500);
        }
    }

    public function snapTest(Request $request)
    {
        // Ambil snap_token dari query string
        $snapToken = "00d1766e-906d-4a21-8673-2274ea0a273e";

        // Pastikan token ada, jika tidak, bisa memberikan pesan error
        if (!$snapToken) {
            return abort(400, 'Snap token is required.');
        }

        // Kirim token ke view
        return view('snap', compact('snapToken'));
    }
}
