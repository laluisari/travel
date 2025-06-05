<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <!-- Container untuk tombol dan form -->
    <div class="mb-4 flex justify-between items-center">
 
        <!-- Tombol Tambah Jadwal -->
        <div class="flex gap-x-4">
            <a href="{{ route('schedules.create') }}"
                class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                + Jadwal per hari
            </a>
            <a href="{{ route('view_generate_schedule') }}"
                class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                + Jadwal per bulan
            </a>
        </div>

        <!-- Form Pencarian -->
        <form method="GET" action="{{ route('schedules.index') }}" class="flex items-center gap-x-2">
            <label for="q" class="text-sm font-medium text-gray-700">Cari:</label>
            <input type="month" name="q" id="q" value="{{ $query }}"
                class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <button type="submit"
                class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Cari
            </button>
        </form>

    </div>

    <!-- Display success message if available -->
    @if (session('success'))
        <div id="success-message"
            class="mb-4 p-4 bg-green-200 text-green-800 rounded-md shadow-md flex justify-between items-center">
            <span>{{ session('success') }}</span>
            <button onclick="document.getElementById('success-message').style.display = 'none'"
                class="ml-4 text-2xl font-bold text-green-800 hover:text-green-600">
                &times; <!-- Cross (X) icon -->
            </button>
        </div>
    @endif

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

    <!-- Responsive Table Wrapper -->
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto bg-white shadow-md rounded-lg overflow-hidden text-sm">

            <thead>
                <tr class="bg-gray-200 text-gray-600">
                    <th class="px-4 py-2 text-left">Travel</th>
                    <th class="px-4 py-2 text-left">Tanggal</th>
                    <th class="px-4 py-2 text-left">Jam Berangkat</th>
                    <th class="px-4 py-2 text-left">Titik Berangkat</th>
                    <th class="px-4 py-2 text-left">Titik Sampai</th>
                    <th class="px-4 py-2 text-left">Aksi</th> <!-- Tambahkan kolom header untuk Aksi -->
                </tr>
            </thead>

            <tbody>
                @foreach ($schedules as $schedule)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $schedule->travel->name }}</td>
                        <td class="px-4 py-2">{{ $schedule->date }}</td>
                        <td class="px-4 py-2">{{ $schedule->time }}</td>
                        <td class="px-4 py-2">{{ $schedule->route->fromLocation->name }}</td>
                        <td class="px-4 py-2">{{ $schedule->route->toLocation->name }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('schedules.edit', $schedule->id) }}"
                                class="text-blue-500 hover:text-blue-700">
                                <i class="fas fa-pencil-alt"></i>
                            </a> |
                            <a href="{{ route('schedules.show', $schedule->id) }}"
                                class="text-green-500 hover:text-green-700">
                                <i class="fas fa-eye"></i>
                            </a> |
                            <form action="{{ route('schedules.destroy', $schedule->id) }}" method="POST"
                                class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700"
                                    onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash-alt"></i> <!-- Ikon Delete -->
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $schedules->links('pagination::tailwind') }}
    </div>
</x-layout>
