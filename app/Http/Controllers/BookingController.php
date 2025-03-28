<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\MidtransService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            ->where('status', 'pending')->orWhere('status', 'paid')
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
            $booking = Booking::where('booking_code', $orderId)->update(['status' => 'paid']);
            Payment::where('booking_id', $booking->id)->update(['status' => 'paid']);
        } elseif ($transactionStatus == 'pending') {
            $booking = Booking::where('booking_code', $orderId)->update(['status' => 'pending']);
            Payment::where('booking_id', $booking->id)->update(['status' => 'pending']);
        } elseif ($transactionStatus == 'expire' || $transactionStatus == 'cancel') {
            $booking = Booking::where('booking_code', $orderId)->update(['status' => 'failed']);
            Payment::where('booking_id', $booking->id)->update(['status' => 'failed']);
        }
        //log
        Log::info('Webhook received', $request->all());
        return new ResponseResource(true, 'Webhook handled', null, 200);
    }
}
