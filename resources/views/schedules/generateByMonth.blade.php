<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <!-- Display Validation Errors -->
    @if ($errors->any())
        <div class="mb-4">
            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-7xl mx-4 bg-white p-6 rounded-lg shadow-lg">
        <form method="POST" action="{{ route('generate_schedule_by_month') }}">
            @csrf
            <div class="mb-4">
                <label for="route_id" class="block text-sm font-medium text-gray-700">Rute:</label>
                <select name="route_id" id="route_id" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="" disabled selected>Pilih Rute</option>
                    @foreach ($routes as $route)
                        <option value="{{ $route['id'] }}">
                            {{ $route['from_location'] }} -> {{ $route['to_location'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="travel_id" class="block text-sm font-medium text-gray-700">Travel:</label>
                <select name="travel_id" id="travel_id" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="" disabled selected>Pilih Travel</option>
                    @foreach ($travels as $travel)
                        <option value="{{ $travel['id'] }}">
                            {{ $travel['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Input Bulan -->
            <div class="mb-4">
                <label for="month" class="block text-sm font-medium text-gray-700">Bulan-tahun:</label>
                <input type="month" name="month" id="month" required placeholder="bulan-tahun"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <!-- Input Waktu Dinamis -->
            <div class="mb-4">
                <label for="time" class="block text-sm font-medium text-gray-700">Waktu:</label>
                <div id="time-container">
                    <div class="flex items-center mb-2">
                        <input type="time" name="time[]" id="time" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <button type="button" onclick="addTimeInput()"
                            class="ml-2 bg-green-500 text-white px-2 py-1 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            +
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit"
                    class="w-full bg-indigo-500 text-white py-2 px-4 rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Simpan</button>
            </div>
        </form>
    </div>

    <script>
        function addTimeInput() {
            const container = document.getElementById('time-container');
            const newInput = document.createElement('div');
            newInput.classList.add('flex', 'items-center', 'mb-2');
            newInput.innerHTML = `
                <input type="time" name="time[]" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <button type="button" onclick="removeTimeInput(this)" 
                    class="ml-2 bg-red-500 text-white px-2 py-1 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    -
                </button>
            `;
            container.appendChild(newInput);
        }
    
        function removeTimeInput(button) {
            button.parentElement.remove();
        }
    </script>

</x-layout>
