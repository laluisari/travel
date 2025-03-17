<?php

namespace Database\Factories;

use App\Models\Seat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Seat>
 */
class SeatFactory extends Factory
{
    
    protected $model = Seat::class; // Hubungkan dengan model Seat

    public function definition(): array
    {
        return [
            'price' => 100000, // Harga tetap
        ];
    }
}
