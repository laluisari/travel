<x-layout>
    {{-- <x-slot:title>{{ $title }}</x-slot:title> --}}

    <div class="max-w-7xl mx-auto my-8 space-y-6">
        <!-- Header Dashboard -->
        <div class="bg-indigo-600 p-6 rounded-xl shadow-lg text-white">
            <h1 class="text-2xl font-bold">Dashboard Transportasi</h1>
            <p class="opacity-90">Ringkasan data dan aktivitas terkini</p>
        </div>

        <!-- Main Content -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Customer Card -->
                <div
                    class="p-6 bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-green-800">Data Customer</h2>
                        <div class="p-3 bg-white rounded-full shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>

                    @foreach ($dataDashboard as $item)
                        @if (isset($item['customer']))
                            <div class="flex items-end">
                                <span class="text-4xl font-bold text-green-700">{{ $item['customer'] }}</span>
                                <span class="ml-2 text-green-600">pengguna</span>
                            </div>
                            <p class="mt-2 text-green-600">Total pelanggan terdaftar dalam sistem</p>
                        @endif
                    @endforeach
                </div>

                <!-- Travel Card -->
                <div
                    class="p-6 bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl shadow-md hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-blue-800">Data Travel</h2>
                        <div class="p-3 bg-white rounded-full shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </div>
                    </div>

                    @foreach ($dataDashboard as $item)
                        @if (isset($item['travel']))
                            <div class="flex items-end">
                                <span class="text-4xl font-bold text-blue-700">{{ $item['travel'] }}</span>
                                <span class="ml-2 text-blue-600">unit</span>
                            </div>
                            <p class="mt-2 text-blue-600">Jumlah armada travel yang beroperasi</p>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Schedule Overview -->
            <div class="mb-6">
                <div
                    class="bg-gradient-to-br from-amber-50 to-amber-100 border border-amber-200 rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-amber-800">Data Jadwal</h2>
                            @foreach ($dataDashboard as $item)
                                @if (isset($item['schedules']))
                                    <p class="text-amber-600">Total <span
                                            class="font-semibold">{{ $item['schedules']['total'] }}</span> jadwal aktif
                                    </p>
                                @endif
                            @endforeach
                        </div>
                        <div class="p-3 bg-white rounded-full shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-amber-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>

                    @foreach ($dataDashboard as $item)
                        @if (isset($item['schedules']['schedules']) && count($item['schedules']['schedules']) > 0)
                            <div class="bg-amber-50 p-4 rounded-lg border border-amber-200">
                                <h3 class="text-lg font-semibold text-amber-800 mb-4">Detail Jadwal Terkini</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach ($item['schedules']['schedules'] as $schedule)
                                        <div
                                            class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-all duration-300 border-l-4 border-amber-400">
                                            <div class="flex items-center mb-3">
                                                <div class="p-2 rounded-full bg-amber-100 mr-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-5 w-5 text-amber-600" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                    </svg>
                                                </div>
                                                <h4 class="font-bold text-gray-800">{{ $schedule['travel'] }}</h4>
                                            </div>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-4 w-4 text-gray-500 mr-2" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                    <div>
                                                        <span class="font-medium text-gray-700">Rute:</span>
                                                        <span class="ml-1">{{ $schedule['route_from'] }}</span>
                                                        <span class="mx-1">→</span>
                                                        <span>{{ $schedule['route_to'] }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-4 w-4 text-gray-500 mr-2" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <div>
                                                        <span class="font-medium text-gray-700">Tanggal:</span>
                                                        <span
                                                            class="ml-1">{{ date('d M Y', strtotime($schedule['date'])) }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-4 w-4 text-gray-500 mr-2" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div>
                                                        <span class="font-medium text-gray-700">Waktu:</span>
                                                        <span
                                                            class="ml-1">{{ date('H:i', strtotime($schedule['time'])) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center mt-8">
                <a href="{{ route('schedules.index') }}"
                    class="inline-flex items-center px-5 py-3 bg-indigo-600 rounded-lg text-white font-semibold shadow-md hover:bg-indigo-700 transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    Lihat Daftar Jadwal
                </a>

                <a href="#"
                    class="inline-flex items-center px-5 py-3 bg-gray-200 rounded-lg text-gray-700 font-semibold shadow-md hover:bg-gray-300 transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh Data
                </a>
            </div>
        </div>
    </div>
</x-layout>
