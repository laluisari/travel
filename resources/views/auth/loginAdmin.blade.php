<x-navbar-admin>
    <x-slot:title></x-slot:title>

    <div class="container mx-auto mt-10 max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6">Login Admin</h1>
        <!-- Display error message if available -->
        @if (session('error'))
            <div id="error-message"
                class="mb-4 p-4 bg-red-200 text-red-800 rounded-md shadow-md flex justify-between items-center">
                <span>{{ session('error') }}</span>
                <button onclick="document.getElementById('error-message').style.display = 'none'"
                    class="ml-4 text-2xl font-bold text-red-800 hover:text-red-600">
                    &times; <!-- Cross (X) icon -->
                </button>
            </div>
        @endif

        <form action="{{ route('admin.login') }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf

            {{-- Input untuk Nomor WhatsApp --}}
            <div class="mb-4">
                <label for="noWa_or_email" class="block text-gray-700 text-sm font-bold mb-2">Whatsapp atau Email</label>
                <input type="text" name="noWa_or_email" id="noWa_or_email" placeholder="08xx atau admin@gmail.com"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    required>
            </div>

            {{-- Input untuk Password --}}
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" id="password" placeholder="Masukkan Password"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    required>
            </div>

            {{-- Tombol Login --}}
            <div class="flex items-center justify-between">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Login
                </button>
            </div>
        </form>


    </div>
</x-navbar-admin>
