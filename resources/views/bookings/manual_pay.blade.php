<x-new-layout>
    <x-slot:title>Detail Kursi</x-slot:title>

    <!-- Display Validation Errors -->
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <!-- Detail Jadwal -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Detail Perjalanan</h2>
                <a href="{{ url()->previous() }}" class="text-indigo-600 hover:text-indigo-800">
                    &larr; Kembali ke Pencarian
                </a>
            </div>

            <div class="bg-gray-50 p-4 rounded-md mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Rute Perjalanan</p>
                        <p class="text-lg font-medium">{{ $scheduleData['from'] }} → {{ $scheduleData['to'] }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Penyedia Travel</p>
                        <p class="text-lg font-medium">{{ $scheduleData['travel_name'] }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tanggal & Waktu</p>
                        <p class="text-lg font-medium">
                            {{ \Carbon\Carbon::parse($scheduleData['date'])->format('d M Y') }} ·
                            {{ $scheduleData['time'] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pilih Kursi -->
        <div class="mb-6">
            <h2 class="text-xl font-bold mb-4">Pilih Kursi</h2>

            <form method="POST" action="{{ route('create_manual_pay') }}" id="booking-form">
                @csrf
                <input type="hidden" name="schedule_id" value="{{ $scheduleData['id'] }}">
                <input type="hidden" name="total_seat" id="total_seat" value="0">
                <input type="hidden" name="total_price" id="total_price" value="0">

                <div class="mb-6">
                    <p class="text-sm text-gray-600 mb-2">Status Kursi:</p>
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center">
                            <div class="w-6 h-6 bg-gray-200 rounded mr-2"></div>
                            <span>Tersedia</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-6 h-6 bg-indigo-500 rounded mr-2"></div>
                            <span>Dipilih</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-6 h-6 bg-red-500 rounded mr-2"></div>
                            <span>Terpesan</span>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <!-- Layout Kursi -->
                    <div class="border border-gray-300 rounded-lg p-6 mb-4">
                        <div class="flex justify-end mb-8">
                            <div class="w-16 h-16 bg-gray-700 rounded-md flex items-center justify-center text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach ($scheduleData['travel_seats'] as $seat)
                                <div class="flex justify-center">
                                    <label for="seat-{{ $seat['id'] }}" class="cursor-pointer">
                                        <input type="checkbox" id="seat-{{ $seat['id'] }}" name="booking_seat_ids[]"
                                            value="{{ $seat['id'] }}" class="hidden seat-checkbox"
                                            {{ $seat['status'] !== 'available' ? 'disabled' : '' }}
                                            data-seat-number="{{ $seat['seat_number'] }}"
                                            data-seat-price="{{ $seat['price'] }}">
                                        <div
                                            class="w-16 h-16 rounded-md flex flex-col items-center justify-center text-center transition-colors
                                            {{ $seat['status'] === 'available'
                                                ? 'bg-gray-200 hover:bg-gray-300'
                                                : ($seat['status'] === 'booked'
                                                    ? 'bg-red-500 text-white cursor-not-allowed'
                                                    : 'bg-red-500 text-white cursor-not-allowed') }}">
                                            <span class="font-medium">{{ $seat['seat_number'] }}</span>
                                            <span
                                                class="text-xs mt-1">{{ number_format($seat['price'], 0, ',', '.') }}</span>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Detail Pemesanan -->
                <div class="bg-gray-50 p-4 rounded-md mb-6">
                    <h3 class="font-medium mb-3">Detail Pemesanan</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Kursi Dipilih:</span>
                            <span id="selected-seats-text">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Jumlah Kursi:</span>
                            <span id="seat-count">0</span>
                        </div>
                        <div class="flex justify-between font-medium">
                            <span>Total Harga:</span>
                            <span id="price-display">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Pilih Metode Pembayaran -->
                <div class="mb-6">
                    <h2 class="text-xl font-bold mb-4">Pilih Metode Pembayaran</h2>
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="payment_method" value="transfer" class="form-radio h-5 w-5 text-indigo-600" required>
                            <span class="ml-2">Transfer</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="payment_method" value="cash" class="form-radio h-5 w-5 text-indigo-600" required>
                            <span class="ml-2">Cash</span>
                        </label>
                    </div>
                </div>

                <!-- Data Pemesan -->
                <div class="mb-6">
                    <h3 class="text-xl font-bold mb-4">Data Pemesan</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                            <input type="text" name="name" id="name" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                value="{{ old('name') }}">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                value="{{ old('email') }}">
                        </div>

                        <div>
                            <label for="no_wa" class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                            <input type="tel" name="no_wa" id="no_wa" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                value="{{ old('no_wa') }}">
                        </div>

                    </div>
                </div>

                <div>
                    <button type="submit" id="continue-button" disabled
                        class="w-full md:w-auto bg-indigo-600 text-white py-2 px-6 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        Lanjutkan Pemesanan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.seat-checkbox');
            const continueButton = document.getElementById('continue-button');
            const seatCountDisplay = document.getElementById('seat-count');
            const priceDisplay = document.getElementById('price-display');
            const selectedSeatsText = document.getElementById('selected-seats-text');
            const totalSeatInput = document.getElementById('total_seat');
            const totalPriceInput = document.getElementById('total_price');

            // Function to update the display
            function updateDisplay() {
                const selectedSeats = document.querySelectorAll('.seat-checkbox:checked');
                const seatCount = selectedSeats.length;

                // Calculate total price based on individual seat prices
                let totalPrice = 0;
                let seatNumbers = [];

                selectedSeats.forEach(seat => {
                    totalPrice += parseInt(seat.dataset.seatPrice);
                    seatNumbers.push(seat.dataset.seatNumber);
                });

                // Update display
                seatCountDisplay.textContent = seatCount;
                priceDisplay.textContent = 'Rp ' + totalPrice.toLocaleString('id-ID');

                // Update hidden inputs
                totalSeatInput.value = seatCount;
                totalPriceInput.value = totalPrice;

                // Update selected seats text
                if (seatCount > 0) {
                    selectedSeatsText.textContent = seatNumbers.join(', ');
                } else {
                    selectedSeatsText.textContent = '-';
                }

                // Enable/disable button
                continueButton.disabled = seatCount === 0;

                // Update selected seats styling
                checkboxes.forEach(checkbox => {
                    const seatDiv = checkbox.nextElementSibling;

                    if (checkbox.checked) {
                        seatDiv.classList.remove('bg-gray-200', 'hover:bg-gray-300');
                        seatDiv.classList.add('bg-indigo-500', 'text-white');
                    } else if (!checkbox.disabled) {
                        seatDiv.classList.remove('bg-indigo-500', 'text-white');
                        seatDiv.classList.add('bg-gray-200', 'hover:bg-gray-300');
                    }
                });
            }

            // Initialize
            updateDisplay();

            // Handle seat selection
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateDisplay();
                });
            });

            // Form submission
            document.getElementById('booking-form').addEventListener('submit', function(e) {
                const selectedSeats = document.querySelectorAll('.seat-checkbox:checked');

                if (selectedSeats.length === 0) {
                    e.preventDefault();
                    alert('Silakan pilih minimal 1 kursi.');
                    return false;
                }

                return true;
            });
        });
    </script>
</x-new-layout>