<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Travel extends Model
{
    protected $guarded = ['id'];
    protected $table = 'travels';

  // Relasi ke kursi melalui tabel pivot travel_seats
  public function seats()
  {
      return $this->belongsToMany(Seat::class, 'travel_seats')->withPivot('status')->withTimestamps();
  }

  public function travel_seats()
  {
      return $this->hasMany(TravelSeat::class);
  }
  
}
