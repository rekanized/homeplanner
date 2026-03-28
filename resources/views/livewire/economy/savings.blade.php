<div>
    <!-- Header & Summary Card -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-6); margin-bottom: var(--space-8);">
        <div>
            <h1 style="font-size: 2.25rem; font-weight: 900; margin-bottom: 4px;">Savings</h1>
            <p style="color: var(--text-muted); font-weight: 600;">Manage your long-term accumulated balances and goals.</p>
        </div>

        <div class="summary-grid" style="margin-bottom: 0; display: flex; gap: var(--space-4); flex-wrap: wrap; justify-content: flex-end;">
            <div class="summary-card accent" style="min-width: 180px; padding: 12px 20px;">
                <p class="summary-label" style="color: rgba(255,255,255,0.7);">Accumulated Savings</p>
                <h2 class="summary-value" style="font-size: 1.5rem;">{{ number_format($this->totalSavings, 0, ',', ' ') }} kr</h2>
            </div>
        </div>
    </div>

    <!-- Savings List -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-weight: 900;">Goals & Locations</h3>
            <button wire:click="addSavingRow" class="eco-add-btn" title="Add saving">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            </button>
        </div>
        <div class="eco-grid-table">
            <div class="eco-grid-header savings-grid">
                <div style="width: 20px;"></div>
                <div>Purpose</div>
                <div>Bank/App</div>
                <div>Saver</div>
                <div style="text-align: right;">Amount</div>
                <div></div>
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
                <div class="eco-grid-row savings-grid" wire:key="saving-{{ $saving->id }}">
                    <div class="eco-drag-handle" style="cursor: grab; color: var(--slate-300); display: flex; align-items: center; justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </div>
                    <div class="eco-mobile-stack">
                        <div class="eco-field">
                            <span class="mobile-label">Purpose</span>
                            <input type="text" class="eco-inline-input" value="{{ $saving->name }}" placeholder="Purpose"
                                wire:change="updateSaving({{ $saving->id }}, 'name', $event.target.value)">
                        </div>
                        <div class="eco-field">
                            <span class="mobile-label">Bank/App</span>
                            <input type="text" class="eco-inline-input" value="{{ $saving->location }}" placeholder="Bank/App"
                                wire:change="updateSaving({{ $saving->id }}, 'location', $event.target.value)">
                        </div>
                    </div>
                    <div class="eco-mobile-stack">
                        <div class="eco-field">
                            <span class="mobile-label">Saver</span>
                            <select class="eco-inline-select" style="width: 100%;"
                                wire:change="updateSaving({{ $saving->id }}, 'saver_id', $event.target.value)">
                                <option value="" @if(!$saving->saver_id) selected @endif>Saver</option>
                                @foreach($this->users as $user)
                                    <option value="{{ $user->id }}" @if($saving->saver_id == $user->id) selected @endif>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="eco-field">
                            <span class="mobile-label">Amount</span>
                            <input type="number" class="eco-inline-input eco-inline-amount" value="{{ $saving->amount }}" placeholder="0" style="width: 100%;"
                                wire:change="updateSaving({{ $saving->id }}, 'amount', $event.target.value)">
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end;">
                        <button wire:click="deleteSaving({{ $saving->id }})" class="eco-delete-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        </button>
                    </div>
                </div>
                @empty
                <div style="text-align: center; color: var(--slate-400); padding: 40px; font-size: 13px;">No savings goals recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
