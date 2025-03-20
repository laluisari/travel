<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <!-- Display Validation Errors -->
    @if ($errors->any())
        <div class="mb-4">
            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-7xl mx-4 bg-white p-6 rounded-lg shadow-lg">
        <form method="POST" action="{{ route('schedules.update', $schedule->id) }}">
            @csrf
            @method('PUT') <!-- Gunakan metode PUT untuk update -->

            <!-- Pilih Rute -->
            <div class="mb-4">
                <label for="route_id" class="block text-sm font-medium text-gray-700">Rute:</label>
                <select name="route_id" id="route_id" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="" disabled>Pilih Rute</option>
                    @foreach ($routes as $route)
                        <option value="{{ $route['id'] }}"
                            {{ old('route_id', $schedule->route_id) == $route['id'] ? 'selected' : '' }}>
                            {{ $route['from_location'] }} -> {{ $route['to_location'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Pilih Travel -->
            <div class="mb-4">
                <label for="travel_id" class="block text-sm font-medium text-gray-700">Travel:</label>
                <select name="travel_id" id="travel_id" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="" disabled>Pilih Travel</option>
                    @foreach ($travels as $travel)
                        <option value="{{ $travel['id'] }}"
                            {{ old('travel_id', $schedule->travel_id) == $travel['id'] ? 'selected' : '' }}>
                            {{ $travel['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Input Tanggal -->
            <div class="mb-4">
                <label for="date" class="block text-sm font-medium text-gray-700">Tanggal:</label>
                <input type="date" name="date" id="date" value="{{ old('date', $schedule->date) }}" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <!-- Input Waktu -->
            <div class="mb-4">
                <label for="time" class="block text-sm font-medium text-gray-700">Waktu:</label>
                <input type="time" name="time" id="time" value="{{ old('time', $schedule->time) }}" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="grid grid-cols-3 md:grid-cols-4 gap-4">
                @foreach ($schedule->travel->seats as $seat)
                    @php
                        $statusColor = match ($seat->pivot->status) {
                            'available' => 'bg-blue-500 border-blue-500 text-white', // Warna biru dengan teks putih
                            'booked' => 'bg-orange-500 border-orange-500 text-white', // Warna oranye dengan teks putih
                            'paid' => 'bg-green-500 border-green-500 text-white', // Warna hijau dengan teks putih
                        };
                    @endphp

                    <!-- Wrapper setiap kursi -->
                    <div x-data="{ editing: false, status: '{{ $seat->pivot->status }}' }"
                        class="relative w-full p-4 py-8 text-center border rounded-md cursor-pointer {{ $statusColor }}"
                        @click="editing = true">
                      
                        <!-- Tampilan default -->
                        <div x-show="!editing" x-transition>
                            <span class="font-bold"> {{ $seat->seat_number }}</span>
                            <br>
                            <span class="text-sm">{{ ucfirst($seat->pivot->status) }}</span>
                        </div>

                        <!-- Select untuk edit status -->
                        <template x-if="editing">
                            <div
                                class="absolute inset-0 flex flex-col justify-center items-center bg-white p-2 rounded-md shadow-lg border">
                                <select x-model="status" name="seats[{{ $seat->seat_number }}]"
                                    class="block w-full px-2 py-1 border rounded-md text-sm" @click.stop>
                                    <option value="available">Available</option>
                                    <option value="booked">Booked</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                        </template>

                    </div>
                @endforeach
            </div>


            <!-- Container untuk tombol -->
            <div class="mt-6 flex justify-between items-center gap-4">

                <!-- Tombol Kembali -->
                <a href="{{ route('schedules.index') }}"
                    class="bg-indigo-500 text-white py-2 px-4 rounded-md hover:bg-indigo-600">
                    Kembali ke Daftar Jadwal
                </a>

                <!-- Tombol Simpan -->
                <button type="submit"
                    class="bg-indigo-500 text-white py-2 px-4 rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 md:w-auto w-full">
                    Simpan Perubahan
                </button>

            </div>
        </form>
    </div>
</x-layout>
