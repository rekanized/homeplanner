<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ request()->routeIs('login') ? __('Sign In') : __('Setup') }} - {{ config('app.name', 'Homeplanner') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
        @livewireStyles
    </head>
    <body class="antialiased">
        <a href="#main-content" class="skip-link">{{ __('Skip to main content') }}</a>
        <main id="main-content" tabindex="-1">
            {{ $slot }}
        </main>
        @livewireScripts
        <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
    </body>
</html>
