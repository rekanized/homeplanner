<div class="animate-in">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-8); padding-bottom: var(--space-6); border-bottom: 1px solid var(--border-color);">
        <div>
            <div class="badge badge-soft" style="color: var(--primary); background: var(--primary-soft); margin-bottom: var(--space-2);">Overview</div>
            <h2 style="font-size: 2.5rem; font-weight: 900; letter-spacing: -0.02em; line-height: 1;">Home Dashboard</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-top: var(--space-1);">Global management of your household ecosystems</p>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Current Session</div>
            <div style="font-size: 14px; font-weight: 700;">{{ now()->format('l, j F') }}</div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="summary-grid">
        <div class="summary-card accent">
            <div class="summary-label" style="color: rgba(255,255,255,0.6);">Monthly Income</div>
            <div class="summary-value">{{ number_format($totalIncome, 0, ',', ' ') }} <span style="font-size: 0.5em; opacity: 0.6;">KR</span></div>
            <div style="font-size: 11px; margin-top: var(--space-3); opacity: 0.8; display: flex; align-items: center; gap: 4px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 7-7 7 7"/><path d="M12 19V5"/></svg>
                Stable Inflow
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Total Savings</div>
            <div class="summary-value" style="color: var(--primary);">{{ number_format($totalSavings, 0, ',', ' ') }} <span style="font-size: 0.5em; opacity: 1;">KR</span></div>
            <div style="font-size: 11px; margin-top: var(--space-3); color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary);"></div>
                Accumulated Capital
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Monthly Expenses</div>
            <div class="summary-value" style="color: var(--danger);">{{ number_format($totalExpenses, 0, ',', ' ') }} <span style="font-size: 0.5em; opacity: 1;">KR</span></div>
            <div style="font-size: 11px; margin-top: var(--space-3); color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--danger);"></div>
                Current Burn Rate
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: var(--space-8);">
        <!-- Economy Chart Placeholder -->
        <div class="card" style="padding: 0; overflow: hidden; border-radius: 28px;">
            <div style="padding: var(--space-6) var(--space-8); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.01);">
                <div>
                    <h3 style="font-size: 18px; font-weight: 900;">Economy Overview</h3>
                    <p style="font-size: 11px; color: var(--text-muted);">Financial health & distributions</p>
                </div>
                <a href="{{ route('economy.index') }}" wire:navigate class="btn btn-primary" style="padding: 8px 16px; font-size: 10px;">Open Manager</a>
            </div>
            <div style="padding: var(--space-8); height: 320px; background: var(--bg-input); display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;">
                <!-- Modern Chart Visualization -->
                <div style="width: 100%; height: 160px; display: flex; align-items: flex-end; gap: 12px; padding: 0 var(--space-4);">
                    <div style="flex: 1; height: 40%; background: var(--primary); border-radius: 8px 8px 4px 4px; opacity: 0.4;"></div>
                    <div style="flex: 1; height: 65%; background: var(--primary); border-radius: 8px 8px 4px 4px; opacity: 0.6;"></div>
                    <div style="flex: 1; height: 50%; background: var(--primary); border-radius: 8px 8px 4px 4px; opacity: 0.4;"></div>
                    <div style="flex: 1; height: 85%; background: var(--primary); border-radius: 8px 8px 4px 4px; opacity: 0.8;"></div>
                    <div style="flex: 1; height: 75%; background: var(--primary); border-radius: 8px 8px 4px 4px;"></div>
                    <div style="flex: 1; height: 95%; background: var(--success); border-radius: 8px 8px 4px 4px;"></div>
                </div>
                <div style="margin-top: 24px; text-align: center;">
                    <div style="font-weight: 900; letter-spacing: 0.05em; text-transform: uppercase; font-size: 13px; color: var(--text-main);">Interactive Analytics</div>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Coming in next update</div>
                </div>
            </div>
        </div>

        <!-- Kids System Placeholder -->
        <div class="card" style="padding: 0; overflow: hidden; border-radius: 28px; opacity: 0.8;">
            <div style="padding: var(--space-6) var(--space-8); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="font-size: 18px; font-weight: 900;">Kids Points System</h3>
                    <p style="font-size: 11px; color: var(--text-muted);">Development & Chore tracking</p>
                </div>
                <div class="badge" style="background: var(--border-strong); color: var(--text-muted);">Standby</div>
            </div>
            <div style="padding: var(--space-8); height: 320px; background: var(--bg-input); display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-muted);">
                <div style="width: 80px; height: 80px; border-radius: 50%; border: 4px solid var(--border-color); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div style="font-weight: 900; letter-spacing: 0.05em; text-transform: uppercase; font-size: 13px;">Subsytem Offline</div>
                <div style="font-size: 11px; opacity: 0.6; margin-top: 4px;">Awaiting module activation</div>
            </div>
        </div>
    </div>
</div>
