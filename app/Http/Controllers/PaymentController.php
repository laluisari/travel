<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;

class PaymentController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Check payment status by order ID
     */
    public function checkStatus($orderId)
    {
        try {
            // Find booking by booking_code
            $booking = Booking::where('booking_code', $orderId)->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            $payment = Payment::where('booking_id', $booking->id)->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            // Check dengan Midtrans jika status masih pending
            if ($payment->status === 'pending') {
                $midtransStatus = $this->midtransService->checkTransactionStatus($orderId);

                if ($midtransStatus['success']) {
                    // Update local status based on Midtrans response
                    if ($midtransStatus['status'] === 'settlement') {
                        $payment->update(['status' => 'paid']);
                        $booking->update(['status' => 'paid']);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'status' => $payment->status,
                'payment_type' => $payment->payment_type,
                'booking_code' => $booking->booking_code
            ]);
        } catch (\Exception $e) {
            Log::error('Payment status check error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error checking payment status'
            ], 500);
        }
    }

    /**
     * Handle Midtrans webhook
     */
    public function webhook(Request $request)
    {
        try {
            $orderId = $request->order_id;
            $transactionStatus = $request->transaction_status;
            $fraudStatus = $request->fraud_status ?? null;

            Log::info('Midtrans Webhook: ', $request->all());

            // Find booking
            $booking = Booking::where('booking_code', $orderId)->first();

            if (!$booking) {
                return response()->json(['message' => 'Booking not found'], 404);
            }

            $payment = Payment::where('booking_id', $booking->id)->first();

            if (!$payment) {
                return response()->json(['message' => 'Payment not found'], 404);
            }

            // Update status based on transaction status
            switch ($transactionStatus) {
                case 'settlement':
                    $payment->update(['status' => 'paid']);
                    $booking->update(['status' => 'paid']);
                    break;

                case 'pending':
                    $payment->update(['status' => 'pending']);
                    $booking->update(['status' => 'pending']);
                    break;

                case 'deny':
                case 'cancel':
                case 'expire':
                    $payment->update(['status' => 'failed']);
                    $booking->update(['status' => 'cancelled']);

                    // Release seats
                    foreach ($booking->bookingSeats as $bookingSeat) {
                        $bookingSeat->travelSeat->update(['status' => 'available']);
                    }
                    break;
            }

            return response()->json(['message' => 'Webhook processed successfully']);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['message' => 'Webhook processing failed'], 500);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index() {}
    public function index2()
    {
        $payments = Payment::with('booking')->paginate(33);
        return new ResponseResource(true, "List of payments", $payments, 200);
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
    public function show(Payment $payment)
    {
        //
    }
    public function show2(Payment $payment)
    {
        try {
            $payment->load('booking');
            return new ResponseResource(true, "Payment detail", $payment, 200);
        } catch (\Exception $e) {
            return new ResponseResource(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        //
    }

    public function history_payment_customer(Request $request)
    {
        try {
            $customerId = auth('customer_api')->id();

            // Ambil data payments berdasarkan customer_id di tabel bookings
            $payments = Payment::whereHas('booking', function ($query) use ($customerId) {
                $query->where('customer_id', $customerId);
            })->with('booking')->paginate(33);

            // Menggunakan transform untuk mengambil travel_seat_id ke dalam booking
            $payments->getCollection()->transform(function ($payment) {
                // Ambil travel_seat_id dari booking_seats dan simpan sebagai array
                $payment->booking->travel_seat_id = $payment->booking->bookingSeats->pluck('travel_seat_id');

                // Hapus bookingSeats jika tidak diperlukan lagi
                unset($payment->booking->bookingSeats);

                return $payment;
            });

            return new ResponseResource(true, "List of payments", $payments, 200);
        } catch (\Exception $e) {
            return new ResponseResource(false, $e->getMessage(), null, 500);
        }
    }
}
