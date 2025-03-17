<?php

namespace Database\Seeders;

use App\Models\Seat;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        //seat

        foreach (range(1, 15) as $seatNumber) {
            Seat::factory()->create([
                'seat_number' => $seatNumber, // Nomor kursi berurutan
            ]);
        }

        //Location
        $locations = [
            'Bandung',
            'Jakarta',
            'Bali',
            'Surabaya',
            'Mataram',
            'Sumbawa'
        ];

        foreach ($locations as $location) {
            \App\Models\Location::factory()->create([
                'name' => $location,
            ]);
        }
    }
}
