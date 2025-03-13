<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <h3 class="text-3xl"></h3>
    <h1 class="text-3xl font-bold mb-4">Daftar Pengguna</h1>
    <div class="mb-4">
        <a href="{{ route('users.create') }}" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Create User
        </a>
        
    </div>
    <table class="min-w-full table-auto bg-white shadow-md rounded-lg overflow-hidden">
        <thead>
            <tr class="bg-gray-200 text-gray-600">
                <th class="px-4 py-2 text-left">Name</th>
                <th class="px-4 py-2 text-left">Email</th>
                <th class="px-4 py-2 text-left">No WhatsApp</th>

            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ $user->name }}</td>
                    <td class="px-4 py-2">{{ $user->email }}</td>
                    <td class="px-4 py-2">{{ $user->no_wa }}</td>


                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $users->links('pagination::tailwind') }}
    </div>
</x-layout>