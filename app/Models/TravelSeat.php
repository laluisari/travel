<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelSeat extends Model
{
    protected $guarded = ['id'];

    public function travel()
    {
        return $this->belongsTo(Travel::class);
    }
    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }
    public function bookingSeats()
    {
        return $this->hasMany(BookingSeat::class);
    }
}
