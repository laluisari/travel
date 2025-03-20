<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seat extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    
    public function travelSeats()
    {
        return $this->hasMany(TravelSeat::class);
    }
}
