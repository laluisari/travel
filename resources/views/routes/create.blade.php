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
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Tambah Rute</h2>
        <form method="POST" action="{{ route('routes.store') }}">
            @csrf
            <div class="mb-4">
                <label for="from_location_id" class="block text-sm font-medium text-gray-700">Titik Berangkat:</label>
                <select name="from_location_id" id="from_location_id" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="" disabled selected>Pilih Titik Berangkat</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-4">
                <label for="to_location_id" class="block text-sm font-medium text-gray-700">Titik Tujuan:</label>
                <select name="to_location_id" id="to_location_id" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="" disabled selected>Pilih Titik Tujuan</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Simpan</button>
            </div>
        </form>
    </div>
</x-new-layout>