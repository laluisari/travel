<div class="flex h-screen bg-white text-gray-800">
    <div id="sidebar"
        class="w-64 bg-indigo-600 text-white flex flex-col justify-between items-center transition-transform duration-300 fixed h-full">
        <div class="text-center border-b border-indigo-400 py-4">
            <h2 class="text-2xl font-bold">Admin Panel</h2>
        </div>
        <nav class="flex flex-col space-y-4 justify-center flex-1 overflow-y-auto">
            <a href="/"
                class="flex items-center p-4 rounded-lg hover:bg-indigo-500 {{ request()->is('/') ? 'bg-indigo-500' : '' }}">
                <i class="fas fa-home mr-4"></i> Home
            </a>
            <a href="/users"
                class="flex items-center p-4 rounded-lg hover:bg-indigo-500 {{ request()->is('users*') ? 'bg-indigo-500' : '' }}">
                <i class="fas fa-user-shield mr-4"></i> Admin
            </a>
            <a href="/seats"
                class="flex items-center p-4 rounded-lg hover:bg-indigo-500 {{ request()->is('seats*') ? 'bg-indigo-500' : '' }}">
                <i class="fas fa-chair mr-4"></i> Kursi
            </a>
            <a href="/locations"
                class="flex items-center p-4 rounded-lg hover:bg-indigo-500 {{ request()->is('locations*') ? 'bg-indigo-500' : '' }}">
                <i class="fas fa-map-marker-alt mr-4"></i> Lokasi
            </a>
            <a href="/routes"
                class="flex items-center p-4 rounded-lg hover:bg-indigo-500 {{ request()->is('routes*') ? 'bg-indigo-500' : '' }}">
                <i class="fas fa-route mr-4"></i> Rute
            </a>
            <a href="/travels"
                class="flex items-center p-4 rounded-lg hover:bg-indigo-500 {{ request()->is('travels*') ? 'bg-indigo-500' : '' }}">
                <i class="fas fa-suitcase-rolling mr-4"></i> Travels
            </a>
            <a href="/schedules"
                class="flex items-center p-4 rounded-lg hover:bg-indigo-500 {{ request()->is('schedules*') ? 'bg-indigo-500' : '' }}">
                <i class="fas fa-calendar-alt mr-4"></i> Jadwal
            </a>
            <a href="/bookings"
                class="flex items-center p-4 rounded-lg hover:bg-indigo-500 {{ request()->is('bookings*') ? 'bg-indigo-500' : '' }}">
                <i class="fas fa-book mr-4"></i> Pesanan
            </a>
        </nav>
    </div>

    <div id="main-content" class="ml-64 flex-1 p-6 bg-gray-50 transition-all duration-300">
        <button id="hamburger" class="md:hidden p-4 focus:outline-none">
            <i class="fas fa-bars text-2xl text-indigo-600"></i>
        </button>
        <!-- Main content -->
    </div>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const hamburger = document.getElementById('hamburger');
    const mainContent = document.getElementById('main-content');

    hamburger.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
        sidebar.classList.toggle('translate-x-0');
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
        }
    });
</script>
