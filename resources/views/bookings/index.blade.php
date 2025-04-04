<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    {{-- <div class="mb-4">
        <a href="{{ route('routes.create') }}"
            class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Tambah Booking
        </a> 
    </div> --}}

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

    <!-- Responsive Table Wrapper -->
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto bg-white shadow-md rounded-lg overflow-hidden text-sm">
            <thead>
                <tr class="bg-gray-200 text-gray-600">
                    <th class="px-4 py-2 text-left">Kode Booking</th>
                    <th class="px-4 py-2 text-left">Nama Penumpang</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-left">Total Kursi</th>
                    <th class="px-4 py-2 text-left">Total Harga</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Tanggal-jam</th>
                    <th class="px-4 py-2 text-left">Rute</th>
                    <th class="px-4 py-2 text-left">Travel</th>

                    <th class="px-4 py-2 text-left">Aksi</th> <!-- Tambahkan kolom header untuk Aksi -->
                </tr>
            </thead>
            <tbody>
                @foreach ($bookings as $booking)

                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $booking->booking_code }}</td>
                        <td class="px-4 py-2">{{ $booking->customer->name }}</td>
                        <td class="px-4 py-2">{{ $booking->customer->email }}</td>
                        <td class="px-4 py-2">{{ $booking->total_seat }}</td>
                        <td class="px-4 py-2">{{ $booking->total_price }}</td>
                        <td class="px-4 py-2">{{ $booking->status }}</td>
                        <td class="px-4 py-2">
                            <p>{{ $booking->schedule->date }} | <br>
                                {{ $booking->schedule->time }}</p>
                        </td>
                        <td class="px-4 py-2">
                            <p>
                                {{ $booking->schedule->route->fromLocation->name }} - <br>
                                {{ $booking->schedule->route->toLocation->name }}</p>
                        </td>
                        <td class="px-4 py-2">{{ $booking->schedule->travel->name }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('routes.edit', $booking->id) }}" class="text-blue-500 hover:text-blue-700">
                                <i class="fas fa-pencil-alt"></i>
                            </a> |
                            <form action="{{ route('routes.destroy', $booking->id) }}" method="POST"
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
        {{ $bookings->links('pagination::tailwind') }}
    </div>
</x-layout>
