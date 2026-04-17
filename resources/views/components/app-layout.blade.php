<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Homeplanner') }}</title>

        <!-- Theme Engine (Instant & Flicker-Free) -->
        <script>
            function initTheme() {
                const isDark = localStorage.getItem('darkMode') === 'true' || 
                    (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
            }

            // Initial and Navigation load
            initTheme();
            document.addEventListener('livewire:navigated', initTheme);

            // Manual Theme Toggle Logic
            window.addEventListener('apply-theme', e => {
                const isDark = e.detail.darkMode;
                
                // Add selective transition class for premium feel
                document.documentElement.classList.add('theme-trans-active');
                
                // Apply theme
                document.documentElement.classList.toggle('dark', isDark);
                localStorage.setItem('darkMode', isDark);

                // Clean up transition class after it finishes (300ms)
                setTimeout(() => {
                    document.documentElement.classList.remove('theme-trans-active');
                }, 400);
            });
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
        <script src="{{ asset('js/sortable.min.js') }}?v={{ filemtime(public_path('js/sortable.min.js')) }}"></script>
        @livewireStyles
    </head>
    <body class="antialiased">
        <div class="layout-container">
            <livewire:sidebar wire:persist="sidebar" />

            <main class="main-content">
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
