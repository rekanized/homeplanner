<div class="p-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div style="margin-bottom: 48px;">
        <h1 style="font-size: 2.25rem; font-weight: 900; margin-bottom: 12px;">Application History</h1>
        <p style="color: var(--text-muted); font-weight: 600;">Track the evolution of Homeplanner through versions and updates.</p>
    </div>

    @if($this->versions->isEmpty())
        <div style="text-align: center; padding: 60px 40px; border-radius: 32px; background: var(--bg-card); border: 2px dashed var(--border-color);">
            <div style="width: 64px; height: 64px; border-radius: 20px; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 900; margin-bottom: 8px;">No version history yet</h3>
            <p style="font-size: 14px; color: var(--text-muted); max-width: 320px; margin: 0 auto;">Version information will appear here once the system is populated.</p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 32px; position: relative;">
            <!-- Timeline vertical line (only on large screens) -->
            <div style="position: absolute; left: 160px; top: 0; bottom: 0; width: 2px; background: var(--border-color); z-index: 0;" class="hidden-mobile"></div>

            @foreach($this->versions as $version)
                <div style="display: flex; gap: 32px; position: relative; z-index: 1;">
                    <!-- Date Column -->
                    <div style="width: 140px; flex-shrink: 0; text-align: right; padding-top: 12px;" class="hidden-mobile">
                        <div style="font-size: 14px; font-weight: 900; color: var(--text-main);">{{ $version['released_at']->format('M j, Y') }}</div>
                        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted);">{{ $version['released_at']->format('H:i') }}</div>
                    </div>

                    <!-- Version Bubble -->
                    <div style="width: 42px; height: 42px; border-radius: 50%; background: var(--bg-body); border: 4px solid var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 0 0 4px var(--bg-body);">
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary);"></div>
                    </div>

                    <!-- Content Card -->
                    <div style="flex: 1; padding: 24px; border-radius: 24px; background: var(--bg-card); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <h3 style="font-size: 1.25rem; font-weight: 900; font-family: var(--font-heading);">{{ $version['version'] }}</h3>
                                <span class="badge badge-soft" style="font-size: 10px; font-weight: 800; background: var(--primary-soft); color: var(--primary);">STABLE</span>
                            </div>
                            <!-- Mobile only date -->
                            <div class="visible-mobile" style="font-size: 11px; font-weight: 700; color: var(--text-muted);">
                                {{ $version['released_at']->format('M j, Y') }}
                            </div>
                        </div>

                        <!-- Changes Grid -->
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            @foreach($version['changes'] as $change)
                                <div style="display: flex; gap: 12px; align-items: flex-start; padding: 12px; border-radius: 16px; background: var(--bg-input);">
                                    <div style="flex-shrink: 0; margin-top: 2px;">
                                        @if($change['type'] === 'added')
                                            <span style="display: inline-flex; width: 16px; height: 16px; border-radius: 4px; background: var(--success); color: white; align-items: center; justify-content: center; font-size: 10px; font-weight: 900;">+</span>
                                        @elseif($change['type'] === 'fixed')
                                            <span style="display: inline-flex; width: 16px; height: 16px; border-radius: 4px; background: var(--warning); color: white; align-items: center; justify-content: center; font-size: 10px; font-weight: 900;">!</span>
                                        @else
                                            <span style="display: inline-flex; width: 16px; height: 16px; border-radius: 4px; background: var(--primary); color: white; align-items: center; justify-content: center; font-size: 10px; font-weight: 900;">•</span>
                                        @endif
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 2px;">
                                            <span style="font-size: 10px; font-weight: 900; text-transform: uppercase; color: {{ $change['type'] === 'added' ? 'var(--success)' : ($change['type'] === 'fixed' ? 'var(--warning)' : 'var(--primary)') }};">
                                                {{ strtoupper($change['type']) }}
                                            </span>
                                        </div>
                                        <p style="font-size: 13px; font-weight: 600; color: var(--text-main); line-height: 1.5;">{{ $change['description'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <style>
        @media (max-width: 768px) {
            .hidden-mobile { display: none !important; }
            .visible-mobile { display: block !important; }
        }
        .visible-mobile { display: none; }
    </style>
</div>
