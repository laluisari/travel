<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $guarded = ['id'];
    protected $tabel = 'bookings';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function bookingSeats()
    {
        return $this->hasMany(BookingSeat::class);
    }
    
    public function travelSeats()
    {
        return $this->belongsToMany(TravelSeat::class, 'booking_seats', 'booking_id', 'travel_seat_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
