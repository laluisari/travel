<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;

class PaymentController extends Controller
{
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
    
            return new ResponseResource(true, "List of payments", $payments, 200);
        } catch (\Exception $e) {
            return new ResponseResource(false, $e->getMessage(), null, 500);
        }
    }
}
