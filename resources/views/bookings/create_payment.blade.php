<x-layout>
    <x-slot:title>Hasil Pencarian Jadwal</x-slot:title>

    <!-- Display Validation Errors -->
    {{-- @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif --}}

    <div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <!-- Form Pencarian -->
        <div class="mb-6">
            <h2 class="text-xl font-bold mb-4">Cari Jadwal</h2>
            <form method="GET" action="{{ route('search_payment') }}">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="from" class="block text-sm font-medium text-gray-700">Titik Berangkat:</label>
                        <select name="from" id="from" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="" disabled {{ !request('from') ? 'selected' : '' }}>Pilih Titik
                                Berangkat</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->name }}"
                                    {{ request('from') == $location->name ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="to" class="block text-sm font-medium text-gray-700">Titik Tujuan:</label>
                        <select name="to" id="to" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="" disabled {{ !request('to') ? 'selected' : '' }}>Pilih Titik Tujuan
                            </option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->name }}"
                                    {{ request('to') == $location->name ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700">Tanggal:</label>
                        <input type="date" name="date" id="date" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            value="{{ request('date', date('Y-m-d')) }}">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit"
                        class="w-full md:w-auto bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Cari Jadwal
                    </button>
                </div>
            </form>
        </div>

        <!-- Hasil Pencarian -->
        @if (isset($schedules) && count($schedules) > 0)
            <div class="mt-6">
                <h2 class="text-xl font-bold mb-4">Jadwal Tersedia</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th
                                    class="px-6 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Travel
                                </th>
                                <th
                                    class="px-6 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rute
                                </th>
                                <th
                                    class="px-6 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal
                                </th>
                                <th
                                    class="px-6 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu
                                </th>
                                <th
                                    class="px-6 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($schedules as $schedule)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $schedule['travel_name'] }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $schedule['from'] }} →
                                            {{ $schedule['to'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($schedule['date'])->format('d M Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $schedule['time'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <form method="GET" action="{{ route('manual_pay', $schedule['id']) }}"
                                            class="inline">
                                            <input type="hidden" name="id_schedule" value="{{ $schedule['id'] }}">
                                            <button type="submit"
                                                class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md">
                                                Lihat Kursi
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif(request()->has('from') && request()->has('to') && request()->has('date'))
            <div class="mt-6 text-center py-8">
                @if ($errors->any())
                <div class="text-xl font-semibold text-gray-700 mb-2">Tidak ada jadwal tersedia</div>
                    <p class="text-gray-500"> {{ $errors->first() }}</p>
                @endif
            </div>
        @endif
    </div>
</x-layout>