<?php

use App\Models\Booking;

function bookingCode($customer_id)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $newBarcode = '';

    do {
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        $newBarcode = "MT" .$customer_id . $randomString;

        $exists = Booking::where('booking_code', $newBarcode)->exists();

    } while ($exists);

    return $newBarcode;
}