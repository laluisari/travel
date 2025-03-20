<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="max-w-7xl mx-4 bg-white p-6 rounded-lg shadow-lg">
        <p><strong>Nama Travel:</strong> {{ $travel->name }}</p>
        <p><strong>Tipe Travel:</strong> {{ $travel->type }}</p>

        <h2 class="text-xl font-bold mt-6 mb-2">Kursi:</h2>
        <div class="grid grid-cols-3 md:grid-cols-4 gap-4">
            @foreach ($travel->seats as $seat)
                <div class="w-full p-4 text-center border border-gray-300 rounded-md bg-gray-100">
                    <span class="font-medium">Kursi Nomor:</span> {{ $seat->seat_number }}
                   
                </div>
            @endforeach
        </div>

        <a href="{{ route('travels.index') }}" class="mt-6 inline-block bg-indigo-500 text-white py-2 px-4 rounded-md hover:bg-indigo-600">
            Kembali ke Daftar Travel
        </a>
    </div>
</x-layout>