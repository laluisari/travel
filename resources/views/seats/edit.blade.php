<x-layout>
    <x-slot:title>Edit Kursi</x-slot:title>
    <form action="{{ route('seats.update', $seat->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="seat_number" class="block text-gray-700">Nomor Kursi</label>
            <input type="text" name="seat_number" id="seat_number" value="{{ old('seat_number', $seat->seat_number) }}"
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            @error('seat_number')
                <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-4">
            <label for="price" class="block text-gray-700">Harga</label>
            <input type="text" id="formatted_price"
                value="{{ number_format(old('price', $seat->price), 0, ',', '.') }}"
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                oninput="formatPrice(this)">
            <input type="hidden" name="price" id="price" value="{{ old('price', $seat->price) }}">
            @error('price')
                <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <button type="submit"
                class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Edit
                Kursi</button>
        </div>
    </form>
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
</x-layout>
