<x-new-layout>
    <x-slot:title>Edit Rute</x-slot:title>

    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Edit Rute</h2>
        <form action="{{ route('routes.update', $route->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="from_location_id" class="block text-sm font-medium text-gray-700">Titik Berangkat:</label>
                <select name="from_location_id" id="from_location_id" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="" disabled>Pilih Titik Berangkat</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" {{ old('from_location_id', $route->from_location_id) == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
                @error('from_location_id')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="to_location_id" class="block text-sm font-medium text-gray-700">Titik Tujuan:</label>
                <select name="to_location_id" id="to_location_id" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="" disabled>Pilih Titik Tujuan</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" {{ old('to_location_id', $route->to_location_id) == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
                @error('to_location_id')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Edit Rute</button>
            </div>
        </form>
    </div>
</x-new-layout>