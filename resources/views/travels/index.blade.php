<x-new-layout>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="mb-4">
        <a href="{{ route('travels.create') }}"
            class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Tambah Travel
        </a> 
    </div>

    <!-- Display success message if available -->
    @if(session('success'))
    <div id="success-message" class="mb-4 p-4 bg-green-200 text-green-800 rounded-md shadow-md flex justify-between items-center">
        <span>{{ session('success') }}</span>
        <button onclick="document.getElementById('success-message').style.display = 'none'" class="ml-4 text-2xl font-bold text-green-800 hover:text-green-600">
            &times; <!-- Cross (X) icon -->
        </button>
    </div>
    @endif

    <!-- Responsive Table Wrapper -->
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto bg-white shadow-md rounded-lg overflow-hidden text-sm">
            <thead>
                <tr class="bg-indigo-500 text-white">
                    <th class="px-4 py-2 text-left">Nama Travel</th>
                    <th class="px-4 py-2 text-left">Tipe Travel</th>
                    <th class="px-4 py-2 text-left">Aksi</th> <!-- Tambahkan kolom header untuk Aksi -->
                </tr>
            </thead>
        
            <tbody>
                @foreach ($travels as $travel)
                    <tr class="border-b hover:bg-gray-100">
                        <td class="px-4 py-2">{{ $travel->name }}</td>
                        <td class="px-4 py-2">{{ $travel->type }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('travels.edit', $travel->id) }}" class="text-blue-500 hover:text-blue-700">
                                <i class="fas fa-pencil-alt"></i>
                            </a> |
                            <form action="{{ route('travels.destroy', $travel->id) }}" method="POST" class="inline-block">
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
        {{ $travels->links('pagination::tailwind') }}
    </div>
</x-new-layout>