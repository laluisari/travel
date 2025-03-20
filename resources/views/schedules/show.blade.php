<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="max-w-7xl mx-4 bg-white p-6 rounded-lg shadow-lg">
        <p><strong>Nama Travel:</strong> {{ $schedule->travel->name }}</p>
        <p><strong>Tipe Travel:</strong> {{ $schedule->travel->type }}</p> 
        <p><strong>Tanggal:</strong> {{ $schedule->date }}</p>
        <p><strong>Jam Berangkat:</strong> {{ $schedule->time }}</p>

        <h2 class="text-xl font-bold mt-6 mb-2">Kursi:</h2>
        <div class="grid grid-cols-3 md:grid-cols-4 gap-4">
            @foreach ($schedule->travel->seats as $seat)
                @php
                    $statusColor = match ($seat->pivot->status) {
                        'available' => 'bg-blue-500 border-blue-500 text-white', // Warna biru untuk kursi tersedia
                        'booked' => 'bg-orange-500 border-orange-500 text-white', // Warna oranye untuk kursi dipesan
                        'paid' => 'bg-green-500 border-green-500 text-white', // Warna hijau untuk kursi terbayar
                        default => 'bg-gray-500 border-gray-500 text-white', // Warna abu-abu untuk status lainnya
                    };
                @endphp

                <div class="w-full p-4 py-8 text-center border rounded-md {{ $statusColor }}">
                    <span class="font-bold"> {{ $seat->seat_number }}</span>
                    <br>
                    <span class="text-sm">{{ ucfirst($seat->pivot->status) }}</span>
                </div>
            @endforeach
        </div>

        <a href="{{ route('schedules.index') }}" class="mt-6 inline-block bg-indigo-500 text-white py-2 px-4 rounded-md hover:bg-indigo-600">
            Kembali ke Daftar Jadwal
        </a>
    </div>
</x-layout>