<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use App\Services\MidtransService;
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
    public function index()
    {
        $bookings = Booking::with('customer', 'schedule')->paginate(33);

        return new ResponseResource(true, "List of bookings", $bookings, 200);
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
        $validator = Validator::make($request->all(), [
            'total_seat' => 'required|integer',
            'total_price' => 'required|numeric',
            'schedule_id' => 'required|exists:schedules,id',
            'booking_seat_ids' => 'required|array', // Ensure booking_seat_ids is an array
            'booking_seat_ids.*' => 'required|exists:travel_seats,id', // Validate each element in the array
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

        try {
            DB::beginTransaction();
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
            }
            $travelSeats = $booking->travelSeats()->get();

            $midtransResponse = $this->midtransService->createTransaction($booking, $request->bank, $travelSeats);

            if ($midtransResponse['success']) {
                $booking->update([
                    'midtrans_transaction_id' => $midtransResponse['transaction_id']
                ]);
                return new ResponseResource(true, 'Booking created successfully', [
                    'booking' => $booking,
                    'payment' => [
                        'transaction_id' => $midtransResponse['transaction_id'],
                        'va_numbers' => $midtransResponse['va_numbers'],
                        'bank' => $midtransResponse['bank'] ?? null,
                        'gross_amount' => $midtransResponse['gross_amount'],
                        'transaction_status' => $midtransResponse['transaction_status'],
                        'transaction_time' => $midtransResponse['transaction_time']
                    ]
                ], 201);
                DB::commit();
            } else {
                $booking->delete();
                return new ResponseResource(false, 'Failed to create booking', $midtransResponse, 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return new ResponseResource(false, 'Failed to create booking', $e->getMessage(), 500);
        }
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
        //
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
        $notification = new \Midtrans\Notification();

        $transactionStatus = $notification->transaction_status;
        $orderId = $notification->order_id;
 
        // Update status transaksi di database
        if ($transactionStatus == 'settlement') {
            Booking::where('booking_code', $orderId)->update(['status' => 'paid']);
        } elseif ($transactionStatus == 'pending') {
            Booking::where('booking_code', $orderId)->update(['status' => 'pending']);
        } elseif ($transactionStatus == 'expire' || $transactionStatus == 'cancel') {
            Booking::where('booking_code', $orderId)->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'Webhook handled successfully']);
    }
}
