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
use Illuminate\Support\Facades\Validator;

class ManualBookingController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function processManualBooking(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'schedule_id' => 'required|exists:schedules,id',
            'booking_seat_ids' => 'required|array|min:1',
            'booking_seat_ids.*' => 'exists:travel_seats,id',
            'total_seat' => 'required|numeric|min:1',
            'total_price' => 'required|numeric|min:1',
            'payment_method' => 'required|in:qris,transfer,cash',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'no_wa' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Cek ketersediaan kursi
        $unavailableSeats = TravelSeat::whereIn('id', $request->booking_seat_ids)
            ->where('status', '!=', 'available')
            ->count();

        if ($unavailableSeats > 0) {
            return redirect()->back()->with('error', 'Beberapa kursi yang Anda pilih sudah tidak tersedia.');
        }

        $userId = auth()->check() ? auth()->user()->id : null;

        DB::beginTransaction();

        try {
            // Create or find customer
            $customer = $this->findOrCreateCustomer($request);

            // Create booking
            $booking = $this->createBooking($request, $customer->id, $userId);

            // Process seats
            $this->processBookingSeats($request, $booking);

            // Handle payment based on method
            $paymentResult = $this->handlePaymentMethod($request, $booking);

            DB::commit();

            return $this->handlePaymentResponse($paymentResult, $booking);

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

    private function createBooking(Request $request, $customerId, $userId)
    {
        $status = $request->payment_method === 'cash' ? 'paid' : 'pending';

        return Booking::create([
            'booking_code' => bookingCode($userId),
            'customer_id' => $customerId,
            'schedule_id' => $request->schedule_id,
            'total_seat' => $request->total_seat,
            'total_price' => $request->total_price,
            'status' => $status,
            'user_id' => $userId,
        ]);
    }

    private function processBookingSeats(Request $request, Booking $booking)
    {
        foreach ($request->booking_seat_ids as $travelSeatId) {
            $booking->bookingSeats()->create([
                'travel_seat_id' => $travelSeatId,
            ]);

            // Update seat status
            TravelSeat::where('schedule_id', $booking->schedule_id)
                ->where('id', $travelSeatId)
                ->update(['status' => 'booked']);
        }
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
                'status' => 'pending'
            ]);

            return [
                'success' => true,
                'type' => 'qris',
                'payment' => $payment,
                'qr_code_url' => $qrisResponse['qr_code_url'],
                'transaction_id' => $qrisResponse['transaction_id']
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
                'status' => 'pending'
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

    private function handlePaymentResponse($paymentResult, Booking $booking)
    {
        switch ($paymentResult['type']) {
            case 'cash':
                return redirect()->route('bookings.show', $booking->id)
                    ->with('success', 'Pemesanan berhasil dibuat! Pembayaran cash telah diterima.');

            case 'qris':
                return view('bookings.payment_qris', [
                    'booking' => $booking,
                    'qr_code_url' => $paymentResult['qr_code_url'],
                    'transaction_id' => $paymentResult['transaction_id']
                ]);

            case 'transfer':
                return view('bookings.payment_transfer', [
                    'booking' => $booking,
                    'va_numbers' => $paymentResult['va_numbers']
                ]);

            default:
                throw new \Exception('Invalid payment response type');
        }
    }
}
