<x-layout>
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

    <div class="max-w-7xl mx-4 bg-white p-6 rounded-lg shadow-lg">
        <form method="POST" action="{{ route('seats.store') }}">
            @csrf
            <div class="mb-4">
                <label for="seat_number" class="block text-sm font-medium text-gray-700">Nomor Kursi:</label>
                <input type="text" name="seat_number" id="seat_number" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            
            <div class="mb-4">
                <label for="price" class="block text-sm font-medium text-gray-700">Harga:</label>
                <input type="text" id="formatted_price" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    oninput="formatPrice(this)">
                <input type="hidden" name="price" id="price">
            </div>
            
            <div>
                <button type="submit"
                    class="w-full bg-indigo-500 text-white py-2 px-4 rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Simpan</button>
            </div>
        </form>
    </div>
    <script>
        function formatPrice(input) {
            // Hapus semua karakter non-digit
            let value = input.value.replace(/\D/g, '');
    
            // Jika angka kurang dari 4 digit, kalikan dengan 1.000
            if (value.length > 0 && value.length <= 3) {
                value = (parseInt(value) * 1000).toString();
            }
    
            // Tambahkan format ribuan untuk tampilan
            input.value = new Intl.NumberFormat('id-ID').format(value);
    
            // Simpan nilai mentah ke hidden input
            document.getElementById('price').value = value;
        }
    </script>
</x-layout>
