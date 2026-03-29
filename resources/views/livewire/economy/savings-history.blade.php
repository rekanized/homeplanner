<div>
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-6); margin-bottom: var(--space-8);">
        <div>
            <h1 style="font-size: 2.25rem; font-weight: 900; margin-bottom: 4px;">{{ __('Savings History') }}</h1>
            <p style="color: var(--text-muted); font-weight: 600;">{{ __('Track the growth of your accumulated wealth.') }}</p>
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
                    <div style="font-size: 12px; font-weight: 900; color: var(--primary);">
                        {{ number_format($snapshot->total_amount, 0, ',', ' ') }} kr
                    </div>
                    <div style="font-size: 10px; color: var(--text-muted); font-weight: 600;">
                        {{ __('Total Balance') }}
                    </div>
                </div>
            </div>
            @empty
            <div class="card" style="padding: 40px 24px; text-align: center; border: 2px dashed var(--border-color); border-radius: 20px;">
                <p style="font-size: 13px; color: var(--text-muted); font-weight: 600;">{{ __('No savings snapshots yet.') }}</p>
            </div>
            @endforelse
        </div>

        <!-- Main Content: Frozen View -->
        <div style="flex: 1 1 500px; min-width: 0;">
            @if($this->selectedSnapshot)
            <div class="animate-in">
                <div style="margin-bottom: 24px; padding: 24px; border-radius: 24px; background: var(--bg-card); border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; overflow: hidden;">
                    <div>
                        <span class="badge badge-soft" style="background: var(--primary-soft); color: var(--primary); font-weight: 800;">{{ __('SAVINGS ARCHIVE') }}</span>
                        <h2 style="font-size: 1.75rem; font-weight: 900; margin-top: 8px;">{{ ucfirst(\Carbon\Carbon::createFromDate($this->selectedSnapshot->year, $this->selectedSnapshot->month, 1)->translatedFormat('F')) }} {{ $this->selectedSnapshot->year }}</h2>
                        <p style="font-size: 12px; color: var(--text-muted); font-weight: 600;">{{ __('Recorded on') }} {{ $this->selectedSnapshot->created_at->translatedFormat('j M Y \k\l. H:i') }}</p>
                    </div>
                    
                    <div style="text-align: right; flex: 1; min-width: 200px;">
                        <p style="font-size: 12px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">{{ __('Total Accumulated') }}</p>
                        <h3 style="font-size: 2rem; font-weight: 900; color: var(--primary); letter-spacing: -0.02em;">{{ number_format($this->selectedSnapshot->total_amount, 0, ',', ' ') }} <span style="font-size: 1.25rem; opacity: 0.7;">kr</span></h3>
                    </div>
                </div>

                <!-- Frozen Data Tables -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    
                    <div class="card" style="border-radius: 24px; overflow: hidden;">
                        <div class="card-header" style="border-radius: 24px 24px 0 0;">
                            <h3 style="font-weight: 900;">{{ __('Accumulated Balances Snapshot') }}</h3>
                        </div>
                        <div class="eco-grid-table">
                            <div class="eco-grid-header frozen-balances-grid" style="border-top: 1px solid var(--border-color);">
                                <div>{{ __('Name') }}</div>
                                <div>{{ __('Location') }}</div>
                                <div>{{ __('Saver') }}</div>
                                <div style="text-align: right;">{{ __('Amount') }}</div>
                            </div>
                            <div class="eco-grid-body">
                                @foreach($this->selectedSnapshot->snapshot_data as $bal)
                                <div class="eco-grid-row frozen-balances-grid" style="padding: 12px 20px; border-bottom: 1px solid var(--border-color); font-size: 13px;">
                                    <div class="eco-field">
                                        <span class="mobile-label">{{ __('Name') }}</span>
                                        <div style="font-weight: 700; color: var(--text-main);">{{ $bal['name'] }}</div>
                                    </div>
                                    <div class="eco-field">
                                        <span class="mobile-label">{{ __('Location') }}</span>
                                        <div style="color: var(--text-muted);">{{ $bal['location'] ?? '—' }}</div>
                                    </div>
                                    <div class="eco-field">
                                        <span class="mobile-label">{{ __('Saver') }}</span>
                                        <div style="color: var(--text-muted);">
                                            @php $saver = collect($this->users)->firstWhere('id', $bal['saver_id']); @endphp
                                            {{ $saver ? $saver->name : '—' }}
                                        </div>
                                    </div>
                                    <div class="eco-field" style="text-align: right;">
                                        <span class="mobile-label">{{ __('Amount') }}</span>
                                        <div style="font-weight: 800; color: var(--primary); font-family: var(--font-heading);">{{ number_format($bal['amount'], 0, ',', ' ') }} {{ __("kr") }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @else
            <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; color: var(--text-muted); opacity: 0.7;">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 24px;"><path d="M15.5 2H8.6c-.4 0-.8.2-1.1.5L4.5 5.5c-.3.3-.5.7-.5 1.1V20c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V6.5c0-.4-.2-.8-.5-1.1s-.7-.5-1.1-.5h-2.9"></path><path d="M15.5 2v4a1 1 0 0 0 1 1h4"></path></svg>
                <h3 style="font-size: 1.25rem; font-weight: 900; margin-bottom: 8px;">{{ __('Select a savings snapshot') }}</h3>
                <p style="font-size: 13px; max-width: 320px;">{{ __('Choose a recording from the list to view how your savings looked in the past.') }}</p>
            </div>
            @endif
        </div>

    </div>
</div>

