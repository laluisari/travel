<x-new-layout>
    <x-slot:title>Edit Travel</x-slot:title>

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
        <form method="POST" action="{{ route('travels.update', $travel->id) }}">
            @csrf
            @method('PUT')

            <!-- Nama Travel -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Travel:</label>
                <input type="text" name="name" id="name" value="{{ old('name', $travel->name) }}" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <!-- Tipe Travel -->
            <div class="mb-4">
                <label for="type" class="block text-sm font-medium text-gray-700">Tipe Travel:</label>
                <input type="text" name="type" id="type" value="{{ old('type', $travel->type) }}" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

       

            <!-- Tombol Simpan -->
            <div>
                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Simpan</button>
            </div>
        </form>
    </div>
</x-new-layout>