<x-app-layout>
    <div class="admin-page-shell" style="max-width: 1280px; margin: 0 auto; padding: 40px 24px;">
        <h1 style="font-size: 1.875rem; font-weight: 700; font-family: var(--font-heading); color: var(--text-main); margin-bottom: 24px;">{{ __('Application Settings') }}</h1>
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div id="auth"><livewire:admin.auth-settings /></div>
            <livewire:admin.module-settings />

            <div class="card" style="padding: 24px;">
                <h3 style="font-weight: 800; margin-bottom: 8px;">{{ __('System Information') }}</h3>
                @php $version = json_decode(file_get_contents(resource_path('data/versions.json')), true)[0]['version'] ?? ''; @endphp
                <p style="color: var(--text-muted); font-size: 14px;">{{ __('Homeplanner') }} {{ $version }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
