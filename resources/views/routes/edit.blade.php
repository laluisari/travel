<x-layout>
    <x-slot:title>Edit Rute</x-slot:title>
    <form action="{{ route('routes.update', $route->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="from_location_id" class="block text-gray-700">Titik Berangkat:</label>
            <select name="from_location_id" id="from_location_id" required
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="" disabled>Pilih Titik Berangkat</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" {{ old('from_location_id', $route->from_location_id) == $location->id ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
            @error('from_location_id')
                <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="mb-4">
            <label for="to_location_id" class="block text-gray-700">Titik Tujuan:</label>
            <select name="to_location_id" id="to_location_id" required
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="" disabled>Pilih Titik Tujuan</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" {{ old('to_location_id', $route->to_location_id) == $location->id ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
            @error('to_location_id')
                <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <button type="submit"
                class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Edit
                Rute</button>
        </div>
    </form>

</x-layout>
