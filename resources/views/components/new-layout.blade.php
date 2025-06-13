<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- Tambahkan file CSS dari Vite -->
    @vite('resources/css/app.css')

    <!-- Font Inter -->
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />

    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="h-full">
    <div class="flex h-screen">
        <x-new-navbar></x-new-navbar> <!-- Panggil komponen navbar -->
        <main class="flex-1 p-6">
            {{ $slot }}
        </main>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const hamburger = document.getElementById('hamburger');

        // Check local storage for sidebar state
        const sidebarState = localStorage.getItem('sidebarOpen') === 'true';
        if (sidebarState) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
        } else {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
        }

        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            sidebar.classList.toggle('translate-x-0');
            // Update local storage based on current state
            const isSidebarOpen = sidebar.classList.contains('translate-x-0');
            localStorage.setItem('sidebarOpen', isSidebarOpen);
        });

        // Ensure sidebar is always visible on larger screens
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                localStorage.setItem('sidebarOpen', 'true');
            }
        });
    </script>
</body>

</html>