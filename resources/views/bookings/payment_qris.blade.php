<x-new-layout>
    <x-slot:title>Pembayaran QRIS</x-slot:title>

    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Pembayaran QRIS</h2>
            <p class="text-gray-600 mt-2">Scan QR Code di bawah ini untuk menyelesaikan pembayaran</p>
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

        <!-- QR Code Section -->
        <div class="flex justify-center mb-6">
            <div class="bg-white p-8 rounded-lg shadow-md border">
                <div class="text-center mb-4">
                    <h3 class="text-lg font-medium">Scan QR Code</h3>
                    <p class="text-sm text-gray-500">Gunakan aplikasi e-wallet Anda</p>
                </div>
                
                <div class="flex justify-center mb-4">
                    @if($qr_code_url)
                        <img src="{{ $qr_code_url }}" alt="QR Code" class="w-64 h-64 border rounded-lg">
                    @else
                        <div class="w-64 h-64 border rounded-lg flex items-center justify-center bg-gray-100">
                            <p class="text-gray-500">QR Code tidak tersedia</p>
                        </div>
                    @endif
                </div>

                <div class="text-center">
                    <p class="text-xs text-gray-500">
                        QR Code akan expire dalam <span id="countdown" class="font-medium text-red-500">15:00</span>
                    </p>
                </div>

                <!-- Debug Info untuk Development -->
                @if(config('app.debug'))
                <div class="mt-4 p-3 bg-gray-100 rounded text-xs">
                    <p><strong>Debug Info:</strong></p>
                    <p>Transaction ID: {{ $transaction_id }}</p>
                    <p>QR URL: {{ $qr_code_url }}</p>
                    <p class="text-blue-600">
                        <strong>Untuk Testing:</strong><br>
                        Copy URL di atas dan paste ke Payment Simulator Midtrans
                    </p>
                </div>
                @endif
            </div>
        </div>

        <!-- Payment Instructions -->
        <div class="bg-blue-50 p-4 rounded-md mb-6">
            <h4 class="font-medium text-blue-800 mb-2">Cara Pembayaran:</h4>
            <ol class="list-decimal list-inside text-sm text-blue-700 space-y-1">
                <li>Buka aplikasi e-wallet (GoPay, DANA, ShopeePay, dll)</li>
                <li>Pilih menu "Scan QR" atau "Bayar"</li>
                <li>Arahkan kamera ke QR Code di atas</li>
                <li>Konfirmasi pembayaran di aplikasi Anda</li>
                <li>Pembayaran akan otomatis terverifikasi</li>
            </ol>
        </div>

        <!-- Testing Instructions untuk Sandbox -->
        @if(config('app.debug'))
        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-md mb-6">
            <h4 class="font-medium text-yellow-800 mb-2">🧪 Testing Mode (Sandbox):</h4>
            <div class="text-sm text-yellow-700 space-y-2">
                <p><strong>Untuk testing pembayaran:</strong></p>
                <ol class="list-decimal list-inside space-y-1">
                    <li>Buka <a href="https://simulator.sandbox.midtrans.com/qris/index" target="_blank" class="text-blue-600 underline">Midtrans Payment Simulator</a></li>
                    <li>Pilih "QRIS" di simulator</li>
                    <li>Masukkan Order ID: <strong class="font-mono bg-gray-200 px-1 rounded">{{ $booking->booking_code }}</strong></li>
                    <li>Klik "Pay" untuk simulate successful payment</li>
                    <li>Atau gunakan Transaction ID: <strong class="font-mono bg-gray-200 px-1 rounded">{{ $transaction_id }}</strong></li>
                </ol>
                <p class="mt-2 text-yellow-600"><strong>Note:</strong> QR Code URL hanya untuk display, gunakan simulator untuk testing</p>
            </div>
        </div>
        @endif

        <!-- Payment Status -->
        <div id="payment-status" class="hidden">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">Pembayaran Berhasil!</span>
                </div>
                <p class="mt-2">Pembayaran Anda telah berhasil diverifikasi. Halaman akan otomatis redirect ke detail booking.</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between items-center">
            <a href="{{ route('bookings.index') }}" class="text-gray-600 hover:text-gray-800">
                ← Kembali ke Daftar Booking
            </a>
            
            <div class="space-x-4">
                <button onclick="refreshQR()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Refresh QR
                </button>
                <button onclick="checkPaymentStatus()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Cek Status Pembayaran
                </button>
            </div>
        </div>
    </div>

    <script>
        let countdownInterval;
        let checkStatusInterval;

        // Countdown timer
        function startCountdown() {
            let timeLeft = 15 * 60; // 15 minutes in seconds
            
            countdownInterval = setInterval(() => {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                
                document.getElementById('countdown').textContent = 
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    document.getElementById('countdown').textContent = 'EXPIRED';
                    alert('QR Code telah expired. Silakan refresh QR Code atau buat booking baru.');
                }
                
                timeLeft--;
            }, 1000);
        }

        // Check payment status
        function checkPaymentStatus() {
            fetch(`/api/payment-status/{{ $transaction_id }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.status === 'settlement') {
                        clearInterval(countdownInterval);
                        clearInterval(checkStatusInterval);
                        
                        document.getElementById('payment-status').classList.remove('hidden');
                        
                        setTimeout(() => {
                            window.location.href = '/bookings/{{ $booking->id }}';
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error checking payment status:', error);
                });
        }

        // Refresh QR Code
        function refreshQR() {
            window.location.reload();
        }

        // Auto check payment status every 5 seconds
        function startAutoCheck() {
            checkStatusInterval = setInterval(checkPaymentStatus, 5000);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            startCountdown();
            startAutoCheck();
        });

        // Cleanup intervals when leaving page
        window.addEventListener('beforeunload', function() {
            clearInterval(countdownInterval);
            clearInterval(checkStatusInterval);
        });
    </script>
</x-new-layout>
