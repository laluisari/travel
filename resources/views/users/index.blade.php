<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <div class="mb-4">
        <a href="{{ route('users.create') }}"
            class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Create User
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
                <tr class="bg-gray-200 text-gray-600">
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-left">No WhatsApp</th>
                    <th class="px-4 py-2 text-left">Aksi</th> <!-- Tambahkan kolom header untuk Aksi -->
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $user->name }}</td>
                        <td class="px-4 py-2">{{ $user->email }}</td>
                        <td class="px-4 py-2">{{ $user->no_wa }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('users.edit', $user->id) }}" class="text-blue-500 hover:text-blue-700">
                                <i class="fas fa-pencil-alt"></i>
                            </a> |
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block">
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
        {{ $users->links('pagination::tailwind') }}
    </div>
</x-layout>