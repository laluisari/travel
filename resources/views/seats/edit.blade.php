<x-new-layout>
    <x-slot:title>Edit Kursi</x-slot:title>

    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Edit Kursi</h2>
        <form action="{{ route('seats.update', $seat->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="seat_number" class="block text-sm font-medium text-gray-700">Nomor Kursi</label>
                <input type="text" name="seat_number" id="seat_number" 
                    value="{{ old('seat_number', $seat->seat_number) }}"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                @error('seat_number')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="formatted_price" class="block text-sm font-medium text-gray-700">Harga</label>
                <input type="text" id="formatted_price" 
                    value="{{ old('price', number_format($seat->price, 0, ',', '.')) }}"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    oninput="formatPrice(this)">
                <input type="hidden" name="price" id="price" value="{{ old('price', $seat->price) }}">
                @error('price')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Edit Kursi</button>
            </div>
        </form>
    </div>

    <script>
        function formatPrice(input) {
            // Hapus semua karakter non-digit
            let value = input.value.replace(/\D/g, '');

            // Tambahkan format ribuan
            input.value = new Intl.NumberFormat('id-ID').format(value);

            // Simpan nilai mentah ke hidden input
            document.getElementById('price').value = value;
        }
    </script>
</x-new-layout>