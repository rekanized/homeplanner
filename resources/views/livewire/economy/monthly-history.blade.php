<div>
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-6); margin-bottom: var(--space-8);">
        <div>
            <h1 style="font-size: 2.25rem; font-weight: 900; margin-bottom: 4px;">{{ __('Monthly History') }}</h1>
            <p style="color: var(--text-muted); font-weight: 600;">{{ __('View and compare past financial snapshots.') }}</p>
        </div>

        <button wire:click="triggerManualSnapshot" class="btn-primary" style="padding: 12px 24px; border-radius: 12px; background: var(--primary); color: white; border: none; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
            {{ __('Capture Now') }}
        </button>
    </div>

    @if (session()->has('message'))
        <div style="background: var(--success-soft); color: var(--success); padding: 12px; border-radius: 12px; margin-bottom: 24px; font-weight: 700; font-size: 13px;">
            {{ session('message') }}
        </div>
    @endif

    <div style="display: flex; flex-wrap: wrap; gap: 32px; align-items: start;">
        
        <!-- Sidebar: Snapshot List -->
        <div style="flex: 0 0 320px; min-width: 0; width: 100%; display: flex; flex-direction: column; gap: 12px;">
            <h3 style="font-size: 12px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">{{ __('Snapshots') }}</h3>
            @forelse($this->snapshots as $snapshot)
            <div wire:click="selectSnapshot({{ $snapshot->id }})" 
                 style="padding: 16px; border-radius: 20px; background: {{ $selectedSnapshotId == $snapshot->id ? 'var(--primary-soft)' : 'var(--bg-card)' }}; border: 1px solid {{ $selectedSnapshotId == $snapshot->id ? 'var(--primary)' : 'var(--border-color)' }}; cursor: pointer; transition: all 0.2s; position: relative; overflow: hidden;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 16px; font-weight: 900; color: {{ $selectedSnapshotId == $snapshot->id ? 'var(--primary)' : 'var(--text-main)' }};">
                            {{ ucfirst(\Carbon\Carbon::createFromDate($snapshot->year, $snapshot->month, 1)->translatedFormat('F')) }} {{ $snapshot->year }}
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); font-weight: 600; margin-top: 2px;">
                            {{ $snapshot->created_at->format('Y-m-d H:i') }}
                        </div>
                    </div>
                    <button wire:click.stop="deleteSnapshot({{ $snapshot->id }})" 
                            style="padding: 6px; border-radius: 8px; background: var(--danger-soft); color: var(--danger); border: none; cursor: pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    </button>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 12px;">
                    <div style="font-size: 10px; font-weight: 800; color: var(--success);">
                        + {{ number_format($snapshot->total_income, 0, ',', ' ') }} kr
                    </div>
                    <div style="font-size: 10px; font-weight: 800; color: var(--danger);">
                        - {{ number_format($snapshot->total_expenses, 0, ',', ' ') }} kr
                    </div>
                </div>
            </div>
            @empty
            <div class="card" style="padding: 40px 24px; text-align: center; border: 2px dashed var(--border-color); border-radius: 20px;">
                <p style="font-size: 13px; color: var(--text-muted); font-weight: 600;">{{ __('No snapshots yet.') }}</p>
            </div>
            @endforelse
        </div>

        <!-- Main Content: Frozen View -->
        <div style="flex: 1 1 500px; min-width: 0;">
            @if($this->selectedSnapshot)
            <div class="animate-in">
                <div style="margin-bottom: 24px; padding: 24px; border-radius: 24px; background: var(--bg-card); border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; overflow: hidden;">
                    <div>
                        <span class="badge badge-soft" style="background: var(--primary-soft); color: var(--primary); font-weight: 800;">{{ __('FROZEN SNAPSHOT') }}</span>
                        <h2 style="font-size: 1.75rem; font-weight: 900; margin-top: 8px;">{{ ucfirst(\Carbon\Carbon::createFromDate($this->selectedSnapshot->year, $this->selectedSnapshot->month, 1)->translatedFormat('F')) }} {{ $this->selectedSnapshot->year }}</h2>
                        <p style="font-size: 12px; color: var(--text-muted); font-weight: 600;">{{ __('Captured on') }} {{ $this->selectedSnapshot->created_at->translatedFormat('j M Y \k\l. H:i') }}</p>
                    </div>
                    
                    <div style="display: flex; gap: 16px; flex-wrap: wrap; flex: 1; justify-content: flex-end; min-width: 280px;">
                        <div class="summary-card" style="min-width: 140px; flex: 1;">
                            <p class="summary-label">{{ __('Income') }}</p>
                            <h3 class="summary-value" style="color: var(--success); font-size: 1.15rem;">{{ number_format($this->selectedSnapshot->total_income, 0, ',', ' ') }}</h3>
                        </div>
                        <div class="summary-card" style="min-width: 140px; flex: 1;">
                            <p class="summary-label">{{ __('Expenses') }}</p>
                            <h3 class="summary-value" style="color: var(--danger); font-size: 1.15rem;">{{ number_format($this->selectedSnapshot->total_expenses, 0, ',', ' ') }}</h3>
                        </div>
                        <div class="summary-card accent" style="min-width: 140px; flex: 1;">
                            <p class="summary-label" style="opacity: 0.8;">{{ __('Remaining') }}</p>
                            <h3 class="summary-value" style="font-size: 1.15rem;">{{ number_format($this->selectedSnapshot->total_income - $this->selectedSnapshot->total_expenses - $this->selectedSnapshot->total_savings, 0, ',', ' ') }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Frozen Data Tables -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    
                    <div class="flex-cards" style="gap: 24px;">
                        <!-- Incomes -->
                        <div class="card" style="flex: 1 1 300px; background: var(--bg-card); border-radius: 24px; overflow: hidden;">
                            <div class="card-header" style="border-radius: 24px 24px 0 0;">
                                <h3 style="font-weight: 900;">{{ __('Income') }}</h3>
                            </div>
                            <div class="eco-grid-table">
                                <div class="eco-grid-header frozen-income-grid">
                                    <span>{{ __('Source') }}</span>
                                    <span style="text-align: right;">{{ __('Amount') }}</span>
                                </div>
                                <div class="eco-grid-body">
                                    @foreach($this->selectedSnapshot->snapshot_data['incomes'] as $inc)
                                    <div class="eco-grid-row frozen-income-grid">
                                        <div class="eco-field">
                                            <span class="mobile-label">{{ __('Source') }}</span>
                                            <span style="font-weight: 700; font-size: 13px;">{{ $inc['name'] }}</span>
                                        </div>
                                        <div class="eco-field" style="text-align: right;">
                                            <span class="mobile-label">{{ __('Amount') }}</span>
                                            <span style="font-weight: 800; color: var(--success); font-size: 13px;">{{ number_format($inc['amount'], 0, ',', ' ') }} kr</span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Savings -->
                        <div class="card" style="flex: 1 1 300px; background: var(--bg-card); border-radius: 24px; overflow: hidden;">
                            <div class="card-header" style="border-radius: 24px 24px 0 0;">
                                <h3 style="font-weight: 900;">{{ __('Monthly Savings') }}</h3>
                            </div>
                            <div class="eco-grid-table">
                                <div class="eco-grid-header frozen-savings-grid">
                                    <span>{{ __('Purpose') }}</span>
                                    <span style="text-align: right;">{{ __('Amount') }}</span>
                                </div>
                                <div class="eco-grid-body">
                                    @foreach($this->selectedSnapshot->snapshot_data['savings'] as $sav)
                                    <div class="eco-grid-row frozen-savings-grid">
                                        <div class="eco-field">
                                            <span class="mobile-label">{{ __('Purpose') }}</span>
                                            <span style="font-weight: 700; font-size: 13px;">{{ $sav['name'] }}</span>
                                        </div>
                                        <div class="eco-field" style="text-align: right;">
                                            <span class="mobile-label">{{ __('Amount') }}</span>
                                            <span style="font-weight: 800; color: var(--primary); font-size: 13px;">{{ number_format($sav['amount'], 0, ',', ' ') }} kr</span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expenses Table -->
                    <div class="card" style="border-radius: 24px; overflow: hidden;">
                        <div class="card-header" style="border-radius: 24px 24px 0 0;">
                            <h3 style="font-weight: 900;">{{ __('Expenses Snapshot') }}</h3>
                        </div>
                        <div class="eco-grid-header frozen-expenses-grid" style="border-top: 1px solid var(--border-color);">
                            <div>{{ __('Name') }}</div>
                            <div>{{ __('Category') }}</div>
                            <div>{{ __('Handling') }}</div>
                            <div style="text-align: right;">{{ __('Amount') }}</div>
                        </div>
                        <div class="eco-grid-body">
                            @foreach($this->selectedSnapshot->snapshot_data['expenses'] as $exp)
                            <div class="eco-grid-row frozen-expenses-grid" style="padding: 12px 20px; border-bottom: 1px solid var(--border-color); font-size: 13px;">
                                <div class="eco-field">
                                    <span class="mobile-label">{{ __('Name') }}</span>
                                    <div style="font-weight: 700; color: var(--text-main);">{{ $exp['name'] }}</div>
                                </div>
                                <div class="eco-field">
                                    <span class="mobile-label">{{ __('Category') }}</span>
                                    <div style="color: var(--text-muted);">{{ $exp['category'] ?? '—' }}</div>
                                </div>
                                <div class="eco-field">
                                    <span class="mobile-label">{{ __('Handling') }}</span>
                                    <div style="display: flex; flex-direction: column; gap: 6px;">
                                        <div style="color: var(--text-muted);">{{ $exp['handling'] ?? '—' }}</div>
                                        @if(($exp['split'] ?? false) || ($exp['delayed'] ?? false) || ($exp['one_time_fee'] ?? false))
                                            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                                @if($exp['split'] ?? false)
                                                    <span class="eco-payer-tag" style="background: var(--blue-500); color: white; font-size: 10px;">{{ __('DELA') }}</span>
                                                @endif
                                                @if($exp['delayed'] ?? false)
                                                    <span class="eco-payer-tag" style="background: var(--amber-500); color: white; font-size: 10px;">{{ __('DRÖJSMÅL') }}</span>
                                                @endif
                                                @if($exp['one_time_fee'] ?? false)
                                                    <span class="eco-payer-tag" style="background: var(--danger); color: white; font-size: 10px;">{{ __('ENGÅNGS') }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="eco-field" style="text-align: right;">
                                    <span class="mobile-label">{{ __('Amount') }}</span>
                                    <div style="font-weight: 800; font-family: var(--font-heading);">{{ number_format($exp['amount'], 0, ',', ' ') }} kr</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
            @else
            <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; color: var(--text-muted); opacity: 0.7;">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 24px;"><path d="M15.5 2H8.6c-.4 0-.8.2-1.1.5L4.5 5.5c-.3.3-.5.7-.5 1.1V20c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V6.5c0-.4-.2-.8-.5-1.1s-.7-.5-1.1-.5h-2.9"></path><path d="M15.5 2v4a1 1 0 0 0 1 1h4"></path></svg>
                <h3 style="font-size: 1.25rem; font-weight: 900; margin-bottom: 8px;">{{ __('Select a snapshot') }}</h3>
                <p style="font-size: 13px; max-width: 320px;">{{ __('Choose a month from the list to view the historical snapshot data.') }}</p>
            </div>
            @endif
        </div>

    </div>
</div>

