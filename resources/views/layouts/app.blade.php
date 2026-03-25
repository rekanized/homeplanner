<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Homeplanner') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

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
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
        <script src="{{ asset('js/sortable.min.js') }}"></script>
        @livewireStyles
    </head>
    <body class="antialiased" x-data="{ mobileMenuOpen: false }">
        <div class="layout-container">
            <!-- Mobile Overlay -->
            <div class="mobile-overlay" x-bind:class="{ 'show': mobileMenuOpen }" x-on:click="mobileMenuOpen = false"></div>

            <!-- Mobile Toggle -->
            <button class="mobile-toggle" x-on:click="mobileMenuOpen = !mobileMenuOpen">
                <svg x-show="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                <svg x-show="mobileMenuOpen" x-cloak xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
            </button>

            <livewire:sidebar wire:persist="sidebar" x-bind:class="{ 'open': mobileMenuOpen }" />

            <main class="main-content">
                <div class="animate-in">
                    {{ $slot }}
                </div>
            </main>
        </div>

        @livewireScripts
    </body>
</html>
