<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\CoreApi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class MidtransService
{
    public function __construct()
    {
        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = true;
    }

    public function createTransaction($booking, $bank, $travelSeats)
    {
        try {
            $params = [
                'payment_type' => 'bank_transfer', 
                'transaction_details' => [
                    'order_id' => $booking->booking_code,
                    'gross_amount' => $booking->total_price
                ],
                'customer_details' => [
                    'first_name' => $booking->customer->name,
                    'email' => $booking->customer->email,
                ],
                'item_details' => $this->prepareItemDetails($travelSeats),
                'bank_transfer' => [
                    'bank' => $bank // Bank yang dipilih oleh pengguna
                ]
            ];

            // Kirim request ke Midtrans
            $response = CoreApi::charge($params);

            return [
                'success' => true,
                'transaction_id' => $response->transaction_id,
                'payment_type' => $response->payment_type,
                'va_numbers' => $response->va_numbers ?? null,
                'merchant_id' => $response->merchant_id,
                'gross_amount' => $response->gross_amount,
                'transaction_status' => $response->transaction_status,
                'transaction_time' => $response->transaction_time
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans Transaction Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function prepareItemDetails($travelSeats)
    {
        $itemDetails = [];
    
        foreach ($travelSeats as $travelSeat) {
            $seat = $travelSeat->seat; // Mengakses detail kursi melalui travelSeat
            $itemDetails[] = [
                'id' => $seat->id,
                'price' => $seat->price, // Harga dari travelSeat
                'quantity' => 1,
                'name' => 'Seat ' . $seat->name, // Nama kursi
            ];
        }
    
        return $itemDetails;
    }

    public function checkTransactionStatus($orderId)
    {
        try {
            $status = CoreApi::status($orderId);
            return [
                'success' => true,
                'status' => $status->transaction_status,
                'payment_type' => $status->payment_type,
                'gross_amount' => $status->gross_amount
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans Status Check Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }


}
