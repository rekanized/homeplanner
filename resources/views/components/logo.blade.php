@props(['size' => 40, 'alt' => ''])

<img src="{{ asset('logo.svg') }}?v={{ filemtime(public_path('logo.svg')) }}"
     width="{{ $size }}" height="{{ $size }}" alt="{{ $alt }}"
     {{ $attributes->merge(['class' => 'app-logo']) }}>
