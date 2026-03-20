<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Homeplanner') }}</title>

        <!-- Dark Mode Detection -->
        <script>
            // Single source of truth for the 'dark' class
            function applyTheme(isDark) {
                if (isDark) {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('darkMode', 'true');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('darkMode', 'false');
                }
            }

            // Initial load - instant to prevent flicker
            const savedTheme = localStorage.getItem('darkMode');
            if (savedTheme === 'true' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }

            // Global listener for theme changes
            window.addEventListener('apply-theme', e => {
                applyTheme(e.detail.darkMode);
            });
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
        @livewireStyles
    </head>
    <body class="antialiased">
        <div class="layout-container">
            <livewire:sidebar wire:persist="sidebar" />

            <main class="main-content animate-in">
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
