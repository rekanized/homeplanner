<div class="animate-in">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-8);">
        <div>
            <h1 style="font-size: 2.25rem; font-weight: 900; margin-bottom: 4px;">Audit Log</h1>
            <p style="color: var(--text-muted); font-weight: 600;">Full history of system changes and user activity.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="border-radius: 24px 24px 0 0;">
            <h3 style="font-weight: 900;">Event Log</h3>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); text-align: left;">
                        <th style="padding: 16px 20px; font-weight: 800; text-transform: uppercase; font-size: 10px; color: var(--slate-400);">Event</th>
                        <th style="padding: 16px 20px; font-weight: 800; text-transform: uppercase; font-size: 10px; color: var(--slate-400);">User</th>
                        <th style="padding: 16px 20px; font-weight: 800; text-transform: uppercase; font-size: 10px; color: var(--slate-400);">Model</th>
                        <th style="padding: 16px 20px; font-weight: 800; text-transform: uppercase; font-size: 10px; color: var(--slate-400);">Changes</th>
                        <th style="padding: 16px 20px; font-weight: 800; text-transform: uppercase; font-size: 10px; color: var(--slate-400);">Time</th>
                    </tr>
                </thead>
                <tbody style="color: var(--text-main);">
                    @foreach($logs as $log)
                    <tr style="border-bottom: 1px solid var(--border-color); vertical-align: top;">
                        <td style="padding: 16px 20px;">
                            @php
                                $color = match($log->event) {
                                    'created' => 'var(--success)',
                                    'updated' => 'var(--primary)',
                                    'deleted' => 'var(--danger)',
                                    default => 'var(--slate-400)'
                                };
                            @endphp
                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; background: {{ $color }}15; color: {{ $color }}; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $color }};"></span>
                                {{ $log->event }}
                            </span>
                        </td>
                        <td style="padding: 16px 20px;">
                            @if($log->user)
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    @if($log->user->avatar)
                                        <img src="{{ $log->user->avatar }}" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                                    @else
                                        <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 900;">
                                            {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span style="font-weight: 700;">{{ $log->user->name }}</span>
                                </div>
                            @else
                                <span style="color: var(--slate-400); font-weight: 600;">System</span>
                            @endif
                        </td>
                        <td style="padding: 16px 20px;">
                            <div style="font-weight: 800; color: var(--slate-500); font-size: 11px; text-transform: uppercase;">{{ str_replace('App\\Models\\', '', $log->auditable_type) }}</div>
                            <div style="font-family: monospace; font-size: 12px;">ID: {{ $log->auditable_id }}</div>
                        </td>
                        <td style="padding: 16px 20px; max-width: 400px;">
                            @if($log->event === 'updated')
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    @foreach($log->new_values ?? [] as $key => $newValue)
                                        <div style="font-size: 11px;">
                                            <span style="font-weight: 900; color: var(--text-muted);">{{ $key }}:</span>
                                            <span style="text-decoration: line-through; color: var(--danger); opacity: 0.6;">{{ is_array($old = $log->old_values[$key] ?? '') ? json_encode($old) : $old }}</span>
                                            <span style="font-weight: 800; color: var(--success);">→ {{ is_array($newValue) ? json_encode($newValue) : $newValue }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($log->event === 'created')
                                <div style="font-style: italic; color: var(--slate-400); font-size: 11px;">Initial entry created</div>
                            @else
                                <div style="font-style: italic; color: var(--danger); font-size: 11px;">Model instance removed</div>
                            @endif
                        </td>
                        <td style="padding: 16px 20px; color: var(--slate-400); font-weight: 600;">
                            <div>{{ $log->created_at->format('Y-m-d') }}</div>
                            <div style="font-size: 11px;">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div style="padding: 24px; border-top: 1px solid var(--border-color); display: flex; justify-content: center; background: var(--bg-main); border-radius: 0 0 24px 24px;">
                {{ $logs->links('livewire.partials.custom-pagination') }}
            </div>
        @endif
    </div>
</div>
