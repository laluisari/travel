<x-new-layout>
    <x-slot:title>Pembayaran Transfer Bank</x-slot:title>

    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Pembayaran Transfer Bank</h2>
            <p class="text-gray-600 mt-2">Transfer ke Virtual Account di bawah ini</p>
        </div>

        <!-- Booking Info -->
        <div class="bg-gray-50 p-4 rounded-md mb-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Kode Booking</p>
                    <p class="font-medium">{{ $booking->booking_code }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Pembayaran</p>
                    <p class="font-medium text-lg text-indigo-600">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Virtual Account Info -->
        @foreach($va_numbers as $va)
        <div class="bg-white border-2 border-indigo-200 rounded-lg p-6 mb-4">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="bg-indigo-100 p-3 rounded-lg mr-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">{{ strtoupper($va->bank) }}</h3>
                        <p class="text-gray-600">Virtual Account</p>
                    </div>
                </div>
                <button onclick="copyToClipboard('{{ $va->va_number }}')" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Copy
                </button>
            </div>

            <div class="bg-gray-50 p-4 rounded-md">
                <p class="text-sm text-gray-600 mb-1">Nomor Virtual Account:</p>
                <p class="text-2xl font-mono font-bold text-gray-800" id="va-{{ $va->bank }}">{{ $va->va_number }}</p>
            </div>
        </div>
        @endforeach

        <!-- Payment Instructions -->
        <div class="bg-blue-50 p-4 rounded-md mb-6">
            <h4 class="font-medium text-blue-800 mb-2">Cara Transfer:</h4>
            <ol class="list-decimal list-inside text-sm text-blue-700 space-y-1">
                <li>Buka aplikasi mobile banking atau ATM</li>
                <li>Pilih menu "Transfer" atau "Bayar"</li>
                <li>Pilih bank sesuai dengan Virtual Account di atas</li>
                <li>Masukkan nomor Virtual Account</li>
                <li>Masukkan nominal sesuai dengan total pembayaran</li>
                <li>Konfirmasi transfer</li>
                <li>Simpan bukti transfer</li>
            </ol>
        </div>

        <!-- Expiry Info -->
        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-md mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="text-yellow-800 font-medium">Perhatian</p>
                    <p class="text-yellow-700 text-sm">Virtual Account akan expired dalam 24 jam. Harap lakukan pembayaran sebelum waktu tersebut.</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between items-center">
            <a href="{{ route('bookings.index') }}" class="text-gray-600 hover:text-gray-800">
                ← Kembali ke Daftar Booking
            </a>
            
            <div class="space-x-4">
                <button onclick="checkPaymentStatus()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Cek Status Pembayaran
                </button>
            </div>
        </div>
    </div>

    <script>
        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Nomor Virtual Account berhasil disalin!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }

        // Check payment status
        function checkPaymentStatus() {
            fetch(`/api/payment-status/{{ $booking->booking_code }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.status === 'settlement') {
                        alert('Pembayaran berhasil! Halaman akan dialihkan ke detail booking.');
                        window.location.href = '/bookings/{{ $booking->id }}';
                    } else {
                        alert('Pembayaran belum diterima. Silakan coba lagi dalam beberapa menit.');
                    }
                })
                .catch(error => {
                    console.error('Error checking payment status:', error);
                    alert('Terjadi kesalahan saat mengecek status pembayaran.');
                });
        }

        // Auto check payment status every 30 seconds
        setInterval(function() {
            fetch(`/api/payment-status/{{ $booking->booking_code }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.status === 'settlement') {
                        alert('Pembayaran berhasil! Halaman akan dialihkan ke detail booking.');
                        window.location.href = '/bookings/{{ $booking->id }}';
                    }
                })
                .catch(error => {
                    console.error('Error in auto-check:', error);
                });
        }, 30000); // Check every 30 seconds
    </script>
</x-new-layout>
