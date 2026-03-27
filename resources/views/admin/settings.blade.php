<x-app-layout>
    <div style="max-width: 1280px; margin: 0 auto; padding: 40px 24px;">
        <h1 style="font-size: 1.875rem; font-weight: 700; font-family: var(--font-heading); color: var(--text-main); margin-bottom: 24px;">Application Settings</h1>
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <livewire:admin.auth-settings />
            <livewire:admin.module-settings />

            <div class="card" style="padding: 24px;">
                <h3 style="font-weight: 800; margin-bottom: 8px;">System Information</h3>
                <p style="color: var(--text-muted); font-size: 14px;">Homeplanner Version 1.0.0. All systems operational.</p>
            </div>
        </div>
    </div>
</x-app-layout>
