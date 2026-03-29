<div>
    <!-- Header & Summary Card -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-6); margin-bottom: var(--space-8);">
        <div>
            <h1 style="font-size: 2.25rem; font-weight: 900; margin-bottom: 4px;">{{ __('Savings') }}</h1>
            <p style="color: var(--text-muted); font-weight: 600;">{{ __('Manage your long-term accumulated balances and goals.') }}</p>
        </div>

        <div style="display: flex; align-items: center; gap: 24px;">
            <!-- View/Edit Toggle Button -->
            <button wire:click="toggleEditMode" class="btn {{ $isEditing ? 'btn-success' : 'btn-primary' }}" style="display: flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 12px; font-weight: 800; border: none; cursor: pointer; transition: all 0.2s;">
                @if($isEditing)
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    {{ __('Finish Editing') }}
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    {{ __('Edit Mode') }}
                @endif
            </button>

            <div class="header-stats" style="flex: 1 1 100%; justify-content: center; margin-top: 16px;">
                <div class="summary-card accent" style="min-width: 150px; padding: 12px 24px; display: flex; flex-direction: column; align-items: center;">
                    <p class="summary-label" style="color: rgba(255,255,255,0.8); white-space: nowrap; font-size: 11px;">{{ __('Accumulated Savings') }}</p>
                    <h2 class="summary-value" style="font-size: 1.5rem; letter-spacing: -0.02em;">{{ number_format($this->totalSavings, 0, ',', ' ') }} kr</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Savings List -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-weight: 900;">{{ __('Goals & Locations') }}</h3>
            @if($isEditing)
            <button wire:click="addSavingRow" class="eco-add-btn" title="{{ __('Add saving') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            </button>
            @endif
        </div>
        <div class="eco-grid-table {{ !$isEditing ? 'view-mode' : '' }}">
            <div class="eco-grid-header savings-grid {{ !$isEditing ? 'savings-view-grid' : '' }}">
                @if($isEditing) <div style="width: 20px;"></div> @endif
                <div>{{ __('Purpose') }}</div>
                <div>{{ __('Bank/App') }}</div>
                <div style="text-align: right;">{{ __('Saver') }}</div>
                <div style="text-align: right;">{{ __('Amount') }}</div>
                @if($isEditing) <div style="width: 40px;"></div> @endif
            </div>
            <div class="eco-grid-body"
                x-init="if (typeof Sortable !== 'undefined') new Sortable($el, {
                    handle: '.eco-drag-handle',
                    animation: 150,
                    onEnd: (evt) => {
                        let ids = Array.from($el.querySelectorAll('[wire\\:key]')).map(el => el.getAttribute('wire:key').split('-')[1]);
                        $wire.reorder('saving', ids);
                    }
                })">
                @forelse($this->savings as $saving)
                <div class="eco-grid-row savings-grid {{ !$isEditing ? 'savings-view-grid' : '' }}" wire:key="saving-{{ $saving->id }}">
                    @if($isEditing)
                    <div class="eco-drag-handle" style="cursor: grab; color: var(--slate-300); display: flex; align-items: center; justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </div>
                    @endif
                    @if($isEditing)
                        <div class="eco-mobile-stack">
                            <div class="eco-field">
                                <span class="mobile-label">{{ __('Purpose') }}</span>
                                <input type="text" class="eco-inline-input" value="{{ $saving->name }}" placeholder="{{ __('Purpose') }}"
                                    wire:change="updateSaving({{ $saving->id }}, 'name', $event.target.value)">
                            </div>
                            <div class="eco-field">
                                <span class="mobile-label">{{ __('Bank/App') }}</span>
                                <input type="text" class="eco-inline-input" value="{{ $saving->location }}" placeholder="{{ __('Bank/App') }}"
                                    wire:change="updateSaving({{ $saving->id }}, 'location', $event.target.value)">
                            </div>
                        </div>
                        <div class="eco-mobile-stack">
                            <div class="eco-field">
                                <span class="mobile-label">{{ __('Saver') }}</span>
                                <select class="eco-inline-select" style="width: 100%;"
                                    wire:change="updateSaving({{ $saving->id }}, 'saver_id', $event.target.value)">
                                    <option value="" @if(!$saving->saver_id) selected @endif>{{ __('Saver') }}</option>
                                    @foreach($this->users as $user)
                                        <option value="{{ $user->id }}" @if($saving->saver_id == $user->id) selected @endif>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="eco-field" style="text-align: right;">
                                <span class="mobile-label">{{ __('Amount') }}</span>
                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 4px;">
                                    <input type="number" class="eco-inline-input eco-inline-amount" value="{{ $saving->amount }}" placeholder="0" style="width: 100%;"
                                        wire:change="updateSaving({{ $saving->id }}, 'amount', $event.target.value)">
                                    <span style="font-weight: 700; color: var(--text-muted);">kr</span>
                                </div>
                            </div>
                        </div>
                        <div class="desktop-only" style="display: flex; justify-content: flex-end; align-items: center;">
                            <button wire:click="deleteSaving({{ $saving->id }})" class="eco-delete-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                    @else
                        {{-- Hybrid Stacking: Two Columns (Name/Bank and Amount/Saver) --}}
                        <div class="eco-mobile-stack">
                            <div class="eco-field" style="min-width: 0;">
                                <span style="font-weight: 700;">{{ $saving->name ?: '—' }}</span>
                            </div>
                            <div class="eco-field" style="min-width: 0;">
                                <span style="font-size: 11px; font-weight: 600; color: var(--text-muted);">{{ $saving->location ?: '—' }}</span>
                            </div>
                        </div>

                        <div class="eco-mobile-stack" style="text-align: right; align-items: flex-end;">
                            <div class="eco-field" style="text-align: right; min-width: 0;">
                                <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">
                                    {{ $this->users->find($saving->saver_id)?->name ?: '—' }}
                                </span>
                            </div>
                            <div class="eco-field" style="text-align: right; min-width: 0;">
                                <div style="font-weight: 900; font-family: var(--font-heading); color: var(--primary);">
                                    {{ number_format($saving->amount, 0, ',', ' ') }} kr
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                @empty
                <div style="text-align: center; color: var(--slate-400); padding: 40px; font-size: 13px;">{{ __('No savings goals recorded yet.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

