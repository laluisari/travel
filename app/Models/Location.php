<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function routesFrom()
    {
        return $this->hasMany(Route::class, 'from_location_id');
    }

    public function routesTo()
    {
        return $this->hasMany(Route::class, 'to_location_id');
    }
}
