<div>
    <div class="animate-in">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-8); padding-bottom: var(--space-6); border-bottom: 1px solid var(--border-color); flex-wrap: wrap; gap: 16px;">
            <div>
                <h2 style="font-size: 2.5rem; font-weight: 900; letter-spacing: -0.02em; line-height: 1;">Kids System</h2>
                <p style="color: var(--text-muted); font-size: 14px; margin-top: var(--space-1);">Manage chores and track rewards for the kids</p>
            </div>
            <div>
                @if(!auth()->user()->is_child)
                    <button wire:click="openAddChoreModal" class="btn btn-primary" style="display: flex; align-items: center; gap: 12px; padding: 12px 24px; border-radius: 16px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                        <span>Assign Chore</span>
                    </button>
                @endif
            </div>
        </div>

        @if (session()->has('message'))
            <div style="background: var(--success-soft); color: var(--success); padding: 16px 24px; border-radius: 20px; margin-bottom: var(--space-8); font-weight: 700; border: 1px solid var(--success); display: flex; align-items: center; gap: 12px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                {{ session('message') }}
            </div>
        @endif

        <!-- Children Summary Grid -->
        <div class="summary-grid">
            @foreach($children as $child)
                <div class="summary-card" style="display: flex; align-items: center; gap: 20px; padding: 24px;">
                    <div style="width: 60px; height: 60px; border-radius: 20px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 900; box-shadow: 0 8px 16px -4px var(--primary-soft);">
                        {{ strtoupper(substr($child->name, 0, 1)) }}
                    </div>
                    <div style="flex: 1;">
                        <div class="summary-label">{{ $child->name }}</div>
                        <div class="summary-value" style="color: var(--primary); margin-bottom: 4px;">{{ $child->accumulated_score }} <span style="font-size: 0.875rem; font-weight: 700; opacity: 0.7;">PTS</span></div>
                        <div style="display: flex; gap: 12px; font-size: 11px; font-weight: 700;">
                            <div style="color: var(--success); display: flex; align-items: center; gap: 4px;" title="Points earned this month">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                                <span>{{ $child->monthly_points }} this month</span>
                            </div>
                            <div style="color: var(--text-muted); display: flex; align-items: center; gap: 4px;" title="Total chores finished">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                <span>{{ $child->total_finished_tasks }} chores</span>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @if(auth()->id() == $child->id || !auth()->user()->is_child)
                            <button wire:click="openUsePointsModal({{ $child->id }})" class="btn" style="background: var(--primary-soft); color: var(--primary); padding: 6px 12px; font-size: 11px; border-radius: 8px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"/></svg>
                                Use Points
                            </button>
                        @endif
                        
                        @if(!auth()->user()->is_child)
                            <button wire:click="openAdjustPointsModal({{ $child->id }})" style="background: var(--bg-input); border: none; width: 100%; padding: 4px; border-radius: 6px; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 10px; gap: 4px; transition: all 0.2s;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                                <span>Settings</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Chores Content -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-8); align-items: start; @media (max-width: 1200px) { grid-template-columns: 1fr; }">
            
            <!-- Pending Chores -->
            <div class="card">
                <div class="card-header">
                    <h3 style="font-weight: 900;">Pending Chores</h3>
                    <span class="badge" style="background: var(--warning-soft); color: var(--warning);">{{ $chores->count() }} Tasks</span>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="eco-grid-table">
                        <div class="eco-grid-header" style="grid-template-columns: 1fr 120px 100px 50px;">
                            <div>Chore / Reward</div>
                            <div style="text-align: center;">Assigned To</div>
                            <div style="text-align: center;">Action</div>
                            <div></div>
                        </div>
                        <div class="eco-grid-body">
                            @forelse($chores as $chore)
                                <div class="eco-grid-row" style="grid-template-columns: 1fr 120px 100px 50px; padding: 12px 16px;">
                                    <div>
                                        <div style="font-weight: 800; color: var(--text-main);">{{ $chore->title }}</div>
                                        <div style="font-size: 11px; font-weight: 900; color: var(--primary); margin-top: 2px;">+{{ $chore->score }} Points</div>
                                        @if($chore->description)
                                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">{{ $chore->description }}</div>
                                        @endif
                                    </div>
                                    <div style="text-align: center;">
                                        <span class="badge badge-soft" style="font-size: 10px;">{{ $chore->user->name }}</span>
                                    </div>
                                    <div style="text-align: center;">
                                        @if(auth()->user()->is_child)
                                            @if(auth()->id() == $chore->user_id)
                                                <button wire:click="completeChore({{ $chore->id }})" class="btn btn-success" style="padding: 8px 16px; font-size: 13px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                    Done
                                                </button>
                                            @endif
                                        @else
                                            <button wire:click="completeChore({{ $chore->id }})" class="btn btn-success" style="padding: 8px 16px; font-size: 13px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                Done
                                            </button>
                                        @endif
                                    </div>
                                    <div style="display: flex; justify-content: flex-end;">
                                        @if(!auth()->user()->is_child)
                                            <button wire:click="deleteChore({{ $chore->id }})" class="eco-delete-btn" style="opacity: 0.5;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 12px; opacity: 0.5;"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                    <div style="font-weight: 700;">No pending chores</div>
                                    <div style="font-size: 12px;">Everything is sparkling clean!</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: var(--space-8);">
                <!-- Completed Chores -->
                <div class="card" style="opacity: 0.85;">
                    <div class="card-header">
                        <h3 style="font-weight: 900;">Recently Finished</h3>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <div class="eco-grid-table">
                            <div class="eco-grid-body">
                                @forelse($completedChores as $chore)
                                    <div class="eco-grid-row" style="grid-template-columns: 1fr 120px 100px; padding: 12px 16px;">
                                        <div>
                                            <div style="font-weight: 700; color: var(--text-muted); text-decoration: line-through;">{{ $chore->title }}</div>
                                            <div style="font-size: 11px; font-weight: 800; color: var(--success);">Earned {{ $chore->score }} Points</div>
                                            <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">By {{ $chore->user->name }} • {{ $chore->completed_at ? $chore->completed_at->diffForHumans() : '' }}</div>
                                        </div>
                                        <div style="text-align: center;">
                                            <div style="padding: 4px 10px; border-radius: 8px; background: var(--success-soft); color: var(--success); font-size: 9px; font-weight: 900; text-transform: uppercase;">Finished</div>
                                        </div>
                                        <div style="text-align: right;">
                                            @if(!auth()->user()->is_child)
                                                <button wire:click="revertChore({{ $chore->id }})" class="btn" style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-muted); padding: 6px 12px; font-size: 11px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                                    Revert
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                                        <div style="font-size: 12px;">History will appear here.</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Used Points List -->
                <div class="card" style="opacity: 0.9;">
                    <div class="card-header">
                        <h3 style="font-weight: 900;">Used Points</h3>
                        <span class="badge" style="background: var(--danger-soft); color: var(--danger);">{{ $redemptions->count() }} Redemptions</span>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <div class="eco-grid-table">
                            <div class="eco-grid-body">
                                @forelse($redemptions as $redemption)
                                    <div class="eco-grid-row" style="grid-template-columns: 1fr 80px 40px; padding: 12px 16px;">
                                        <div>
                                            <div style="font-weight: 800; color: var(--text-main);">{{ $redemption->description }}</div>
                                            <div style="font-size: 11px; font-weight: 900; color: var(--danger);">Spent {{ $redemption->score }} Points</div>
                                            <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">Used by {{ $redemption->user->name }} • {{ $redemption->created_at->diffForHumans() }}</div>
                                        </div>
                                        <div style="text-align: center;">
                                            <div style="padding: 4px 10px; border-radius: 8px; background: var(--danger-soft); color: var(--danger); font-size: 9px; font-weight: 900; text-transform: uppercase;">Used</div>
                                        </div>
                                        <div style="display: flex; justify-content: flex-end;">
                                            @if(!auth()->user()->is_child)
                                                <button wire:click="deleteRedemption({{ $redemption->id }})" class="eco-delete-btn" style="opacity: 0.4;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                                        <div style="font-size: 12px;">Spending history will appear here.</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modals (Moved outside animated container to fix fixed positioning) -->
    
    <!-- Assign Chore Modal -->
    @if($showAddChoreModal)
    <div class="modal-overlay" @click.self="$wire.set('showAddChoreModal', false)">
        <div class="modal-content animate-in" style="max-width: 500px; text-align: left; padding: 0;">
            <div style="padding: 24px 32px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.01);">
                <h3 style="font-size: 1.5rem; font-weight: 900; color: var(--text-main);">Assign Chore</h3>
                <button @click="$wire.set('showAddChoreModal', false)" style="background: var(--bg-input); border: none; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 20px;">×</button>
            </div>
            
            <form wire:submit.prevent="addChore" style="padding: 32px; display: flex; flex-direction: column; gap: 24px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Chore Title</label>
                    <input type="text" wire:model="title" style="background: var(--bg-input); color: var(--text-main); border: 2px solid var(--border-color); padding: 14px 18px; border-radius: 16px; width: 100%; font-size: 15px; font-weight: 600; outline: none; transition: border-color 0.2s;" placeholder="E.g. Vacuum the living room" autofocus>
                    @error('title') <p style="font-size: 11px; color: var(--danger); margin-top: 6px; font-weight: 700;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Description (Optional)</label>
                    <textarea wire:model="description" style="background: var(--bg-input); color: var(--text-main); border: 2px solid var(--border-color); padding: 14px 18px; border-radius: 16px; width: 100%; font-size: 15px; font-weight: 600; outline: none; min-height: 100px; resize: vertical;" placeholder="Add some details..."></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 12px;">Assigned To</label>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            @foreach($children as $child)
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; background: var(--bg-input); padding: 10px 14px; border-radius: 12px; border: 1px solid var(--border-color); transition: all 0.2s;">
                                    <input type="checkbox" wire:model="assigned_to" value="{{ $child->id }}" style="width: 18px; height: 18px; accent-color: var(--primary);">
                                    <span style="font-size: 14px; font-weight: 700; color: var(--text-main);">{{ $child->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('assigned_to') <p style="font-size: 11px; color: var(--danger); margin-top: 6px; font-weight: 700;">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Reward Points</label>
                        <div style="position: relative;">
                            <input type="number" wire:model="score" style="background: var(--bg-input); color: var(--text-main); border: 2px solid var(--border-color); padding: 14px 44px 14px 18px; border-radius: 16px; width: 100%; font-size: 15px; font-weight: 800; outline: none;">
                            <span style="position: absolute; right: 18px; top: 50%; transform: translateY(-50%); font-size: 10px; font-weight: 900; color: var(--primary);">PTS</span>
                        </div>
                        @error('score') <p style="font-size: 11px; color: var(--danger); margin-top: 6px; font-weight: 700;">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 12px;">
                    <button type="button" @click="$wire.set('showAddChoreModal', false)" class="btn" style="flex: 1; background: var(--bg-input); color: var(--text-main); border: 1px solid var(--border-color); padding: 16px; border-radius: 18px; font-weight: 800;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex: 2; padding: 16px; border-radius: 18px; font-weight: 900; font-size: 16px; box-shadow: var(--shadow-lg);">Assign Chore</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Adjustment Modal -->
    @if($showAdjustPointsModal)
    <div class="modal-overlay" @click.self="$wire.set('showAdjustPointsModal', false)">
        <div class="modal-content animate-in" style="max-width: 400px; text-align: left; padding: 0;">
            <div style="padding: 24px 32px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 1.25rem; font-weight: 900; color: var(--text-main);">Adjust Points</h3>
                <button @click="$wire.set('showAdjustPointsModal', false)" style="background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 24px;">×</button>
            </div>
            
            <div style="padding: 32px; display: flex; flex-direction: column; gap: 24px;">
                <div style="text-align: center;">
                    <div style="font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Adjusting Score For</div>
                    <div style="font-size: 1.5rem; font-weight: 900; color: var(--primary);">{{ $adjustUserName }}</div>
                </div>

                <div style="display: flex; background: var(--bg-input); padding: 6px; border-radius: 12px; border: 1px solid var(--border-color);">
                    <button type="button" wire:click="$set('adjustType', 'add')" style="flex: 1; padding: 10px; border-radius: 8px; border: none; cursor: pointer; font-weight: 800; font-size: 13px; transition: all 0.2s; @if($adjustType == 'add') background: var(--success); color: white; @else background: transparent; color: var(--text-muted); @endif">
                        Add Points
                    </button>
                    <button type="button" wire:click="$set('adjustType', 'remove')" style="flex: 1; padding: 10px; border-radius: 8px; border: none; cursor: pointer; font-weight: 800; font-size: 13px; transition: all 0.2s; @if($adjustType == 'remove') background: var(--danger); color: white; @else background: transparent; color: var(--text-muted); @endif">
                        Remove Points
                    </button>
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Point Amount</label>
                    <div style="position: relative;">
                        <input type="number" wire:model="adjustAmount" style="background: var(--bg-input); color: var(--text-main); border: 2px solid var(--border-color); padding: 14px 44px 14px 18px; border-radius: 16px; width: 100%; font-size: 15px; font-weight: 800; outline: none;" placeholder="e.g. 10">
                        <span style="position: absolute; right: 18px; top: 50%; transform: translateY(-50%); font-size: 10px; font-weight: 900; color: var(--text-muted);">PTS</span>
                    </div>
                </div>

                <div style="display: flex; gap: 12px;">
                    <button type="button" @click="$wire.set('showAdjustPointsModal', false)" class="btn" style="flex: 1; background: var(--bg-input); color: var(--text-main); border: 1px solid var(--border-color); border-radius: 14px;">Cancel</button>
                    <button type="button" wire:click="adjustPoints" class="btn @if($adjustType == 'add') btn-success @else btn-primary @endif" style="flex: 2; border-radius: 14px; @if($adjustType == 'remove') background: var(--danger); @endif">
                        Apply Change
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Use Points Modal -->
    @if($showUsePointsModal)
    <div class="modal-overlay" @click.self="$wire.set('showUsePointsModal', false)">
        <div class="modal-content animate-in" style="max-width: 450px; text-align: left; padding: 0;">
            <div style="padding: 24px 32px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: rgba(37, 99, 235, 0.05);">
                <div>
                    <h3 style="font-size: 1.5rem; font-weight: 900; color: var(--primary);">Use Points</h3>
                    <p style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px;">Redeem points for rewards</p>
                </div>
                <button @click="$wire.set('showUsePointsModal', false)" style="background: white; border: 1px solid var(--border-color); width: 36px; height: 36px; border-radius: 50%; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 20px;">×</button>
            </div>
            
            <form wire:submit.prevent="usePoints" style="padding: 32px; display: flex; flex-direction: column; gap: 24px;">
                <div style="text-align: center; background: var(--bg-input); padding: 16px; border-radius: 20px; border: 1px solid var(--border-color);">
                    <div style="font-size: 10px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Redeeming for</div>
                    <div style="font-size: 1.25rem; font-weight: 900; color: var(--text-main);">{{ $redemptionUserName }}</div>
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">What are you using them for?</label>
                    <input type="text" wire:model="redemptionDescription" style="background: var(--bg-input); color: var(--text-main); border: 2px solid var(--border-color); padding: 14px 18px; border-radius: 16px; width: 100%; font-size: 15px; font-weight: 600; outline: none;" placeholder="e.g. 1 hour of video games" autofocus>
                    @error('redemptionDescription') <p style="font-size: 11px; color: var(--danger); margin-top: 6px; font-weight: 700;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Point Cost</label>
                    <div style="position: relative;">
                        <input type="number" wire:model="redemptionPoints" style="background: var(--bg-input); color: var(--text-main); border: 2px solid var(--border-color); padding: 14px 44px 14px 18px; border-radius: 16px; width: 100%; font-size: 18px; font-weight: 900; outline: none;">
                        <span style="position: absolute; right: 18px; top: 50%; transform: translateY(-50%); font-size: 12px; font-weight: 900; color: var(--primary);">PTS</span>
                    </div>
                    @error('redemptionPoints') <p style="font-size: 11px; color: var(--danger); margin-top: 6px; font-weight: 700;">{{ $message }}</p> @enderror
                </div>

                <div style="display: flex; gap: 12px; margin-top: 12px;">
                    <button type="button" @click="$wire.set('showUsePointsModal', false)" class="btn" style="flex: 1; background: var(--bg-input); color: var(--text-main); border: 1px solid var(--border-color); padding: 16px; border-radius: 18px;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex: 2; padding: 16px; border-radius: 18px; font-size: 16px;">Confirm Redemption</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
