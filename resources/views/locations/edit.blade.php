<x-layout>
    <x-slot:title>Edit Lokasi</x-slot:title>
    <form action="{{ route('locations.update', $location->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-gray-700">Nomor Kursi</label>
            <input type="text" name="name" id="name" value="{{ old('name', $location->name) }}"
                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            @error('name')
                <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>


        <div class="mb-4">
            <button type="submit"
                class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Edit
                Lokasi</button>
        </div>
    </form>

</x-layout>
