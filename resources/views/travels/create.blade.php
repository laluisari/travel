<x-new-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <!-- Display Validation Errors -->
    @if ($errors->any())
        <div class="mb-4">
            <div class="text-red-600 font-medium">Whoops! Something went wrong.</div>
            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <form method="POST" action="{{ route('travels.store') }}">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Travel:</label>
                <input type="text" name="name" id="name" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="mb-4">
                <label for="type" class="block text-sm font-medium text-gray-700">Tipe Travel:</label>
                <input type="text" name="type" id="type" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kursi:</label>
                <div class="grid grid-cols-3 md:grid-cols-4 gap-2"> <!-- Grid 3-4 kolom -->
                    @foreach ($seats as $seat)
                        <label class="relative cursor-pointer">
                            <!-- Checkbox untuk kursi -->
                            <input type="checkbox" name="seat_ids[]" value="{{ $seat->id }}" class="peer hidden">
                            <!-- Tombol kursi -->
                            <div class="w-full p-4 text-center border border-gray-300 rounded-md transition duration-200
                                peer-checked:bg-green-500 peer-checked:text-white peer-checked:border-green-500
                                hover:bg-blue-200 hover:border-blue-500">
                                {{ $seat->seat_number }}
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('seat_ids')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Simpan</button>
            </div>
        </form>
    </div>

</x-new-layout>