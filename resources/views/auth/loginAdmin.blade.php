<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="h-full flex items-center justify-center bg-gray-50">

    <div class="w-full max-w-md bg-white shadow-lg rounded-lg p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-blue-600">Login Admin</h1>
        </div>

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md shadow-md">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.login') }}" method="POST">
            @csrf
            <!-- Input untuk Nomor WhatsApp atau Email -->
            <div class="mb-4">
                <label for="noWa_or_email" class="block text-gray-700 font-medium mb-2">Whatsapp atau Email</label>
                <input type="text" name="noWa_or_email" id="noWa_or_email" placeholder="08xx atau admin@gmail.com"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                    required>
            </div>

            <!-- Input untuk Password -->
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                <input type="password" name="password" id="password" placeholder="Masukkan Password"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                    required>
            </div>

            <!-- Tombol Login -->
            <div class="flex items-center justify-between">
                <button type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                    Login
                </button>
            </div>
        </form>
    </div>

</body>
</html>
