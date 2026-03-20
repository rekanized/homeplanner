<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    </head>
    <body>
        <header>
            @if (Route::has('login'))
                <nav>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-outline">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-outline">Register</a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <main>
            <div class="content-section">
                <h1>Let's get started</h1>
                <p>Welcome to Home Planner. Start by exploring the following resources:</p>
                
                <ul>
                    <li class="step-item">
                        <div class="dot-outer"><div class="dot-inner"></div></div>
                        <div>
                            Read the 
                            <a href="https://laravel.com/docs" target="_blank" class="link-accent">Documentation</a>
                        </div>
                    </li>
                    <li class="step-item">
                        <div class="dot-outer"><div class="dot-inner"></div></div>
                        <div>
                            Watch video tutorials at 
                            <a href="https://laracasts.com" target="_blank" class="link-accent">Laracasts</a>
                        </div>
                    </li>
                </ul>

                <a href="https://cloud.laravel.com" target="_blank" class="btn btn-primary" style="width: fit-content;">Deploy now</a>

                <p class="footer-text">
                    v{{ app()->version() }} (PHP v{{ PHP_VERSION }})
                    <a href="https://github.com/laravel/laravel" target="_blank" class="link-accent" style="margin-left: 0.5rem;">Repository</a>
                </p>
            </div>
            
            <div class="visual-section">
                <!-- Premium visual element or logo could go here -->
                <svg width="200" height="200" viewBox="0 0 438 104" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: var(--accent-color);">
                    <path d="M17.2036 -3H0V102.197H49.5189V86.7187H17.2036V-3Z" fill="currentColor" />
                    <path d="M88.7131 30.3618C83.4247 30.3618 78.5885 31.3389 74.201 33.2923C69.8111 35.2456 66.0474 37.928 62.9059 41.3333C59.7643 44.7401 57.3198 48.6726 55.5754 53.1293C53.8287 57.589 52.9572 62.274 52.9572 67.1813C52.9572 72.1925 53.8287 76.8995 55.5754 81.3069C57.3191 85.7173 59.7636 89.6241 62.9059 93.0293C66.0474 96.4361 69.8119 99.1155 74.201 101.069C78.5885 103.022 83.4247 103.999 88.7131 103.999C92.8016 103.999 96.8667 102.997 100.905 100.994C104.945 98.9911 108.061 96.2359 110.256 92.7282V102.195H126.563V32.1642H110.256V41.6337ZM110.256 67.1821C110.256 70.1868 109.756 73.0421 108.76 75.7472C107.762 78.4531 106.366 80.8078 104.572 82.8112C102.776 84.8161 100.606 86.4183 98.0637 87.6206C95.5202 88.823 92.7004 89.4238 89.6103 89.4238C86.5178 89.4238 83.7252 88.823 81.2324 87.6206C78.7388 86.4183 76.5949 84.8161 74.7998 82.8112C73.004 80.8078 71.6319 78.4531 70.6856 75.7472C69.7356 73.0421 69.2644 70.1868 69.2644 67.1821C69.2644 64.1758 69.7356 61.3205 70.6856 58.6154C71.6319 55.9102 73.004 53.5571 74.7998 51.5522C76.5949 49.5495 78.7380 47.9451 81.2324 46.7427C83.7252 45.5404 86.5178 44.9396 89.6103 44.9396C92.7012 44.9396 95.5202 45.5404 98.0637 46.7427C100.606 47.9451 102.776 49.5487 104.572 51.5522C106.367 53.5571 107.762 55.9102 108.76 58.6154C109.756 61.3205 110.256 64.1758 110.256 67.1821Z" fill="currentColor" />
                </svg>
            </div>
        </main>

        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
