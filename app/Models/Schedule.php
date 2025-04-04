<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $guarded = ['id'];

    public function travel()
    {
        return $this->belongsTo(Travel::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    
    public function travelSeats()
    {
        return $this->hasMany(TravelSeat::class);
    }
}
