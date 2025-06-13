<x-new-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    
    <div class="mb-4 flex justify-between items-center">
        <h2 class="text-2xl font-semibold text-gray-800">Daftar Admin</h2>
        <a href="{{ route('users.create') }}"
           class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring focus:ring-indigo-300">
            Tambah Admin
        </a>
    </div>

    <!-- Display success message if available -->
    @if (session('success'))
        <div id="success-message" class="mb-4 p-4 bg-green-200 text-green-800 rounded-md shadow-md flex justify-between items-center">
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
            <thead class="bg-indigo-500 text-white">
                <tr>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-left">No WhatsApp</th>
                    <th class="px-4 py-2 text-left">Aksi</th> <!-- Column for actions -->
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($users as $user)
                    <tr class="hover:bg-gray-100">
                        <td class="px-4 py-2">{{ $user->name }}</td>
                        <td class="px-4 py-2">{{ $user->email }}</td>
                        <td class="px-4 py-2">{{ $user->no_wa }}</td>
                        <td class="px-4 py-2 flex space-x-2">
                            <a href="{{ route('users.edit', $user->id) }}" class="text-blue-500 hover:text-blue-700">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash-alt"></i> <!-- Delete icon -->
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
</x-new-layout>