<?php

namespace Database\Seeders;

use App\Models\Seat;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'no_wa' => '087894521051',
        ]);

        //seat

        foreach (range(1, 15) as $seatNumber) {
            Seat::factory()->create([
                'seat_number' => $seatNumber, // Nomor kursi berurutan
            ]);
        }

        //Location
        $locations = [
    
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
