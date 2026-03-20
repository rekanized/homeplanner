<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" 
      x-init="
        $watch('darkMode', val => {
            localStorage.setItem('darkMode', val);
        });
        window.addEventListener('dark-mode-toggled', e => {
            darkMode = e.detail.darkMode;
        });
      "
      :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Homeplanner') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
        @livewireStyles
    </head>
    <body class="antialiased animate-in">
        <div class="layout-container">
            <livewire:sidebar />

            <main class="main-content">
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
