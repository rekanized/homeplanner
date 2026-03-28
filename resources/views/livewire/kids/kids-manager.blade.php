<div>
    <div class="animate-in">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-8); padding-bottom: var(--space-6); border-bottom: 1px solid var(--border-color); flex-wrap: wrap; gap: 16px;">
            <div>
                <h2 class="responsive-title">Kids System</h2>
                <p style="color: var(--text-muted); font-size: 14px; margin-top: var(--space-1);">Manage chores and track rewards for the kids</p>
            </div>
            <div style="display: flex; gap: 12px; align-items: center;">
                @if(!auth()->user()->is_child)
                    <button wire:click="openManageTemplatesModal" class="btn hidden-mobile" style="display: flex; align-items: center; gap: 8px; padding: 12px 18px; border-radius: 16px; background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span>Templates</span>
                    </button>
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
                    <div class="hidden-mobile" style="width: 60px; height: 60px; border-radius: 20px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 900; box-shadow: 0 8px 16px -4px var(--primary-soft);">
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
                    <div style="display: flex; flex-direction: column; gap: 8px; width: 140px;">
                        @if(auth()->id() == $child->id || !auth()->user()->is_child)
                            <button wire:click="openUsePointsModal({{ $child->id }})" class="btn" style="background: var(--primary-soft); color: var(--primary); padding: 8px 14px; font-size: 11px; font-weight: 800; border-radius: 10px; display: flex; align-items: center; gap: 6px; width: 100%; justify-content: center; transition: all 0.2s;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"/></svg>
                                <span>USE POINTS</span>
                            </button>
                        @endif

                        @if(!auth()->user()->is_child)
                            <button wire:click="openQuickAssignModal({{ $child->id }})" class="btn" style="background: var(--success-soft); color: var(--success); padding: 8px 14px; font-size: 11px; font-weight: 800; border-radius: 10px; display: flex; align-items: center; gap: 6px; width: 100%; justify-content: center; transition: all 0.2s;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                                <span>Assign Chore</span>
                            </button>

                            <button wire:click="openAdjustPointsModal({{ $child->id }})" class="btn" style="background: var(--bg-input); color: var(--text-muted); border: 1px solid var(--border-color); padding: 8px 14px; font-size: 11px; font-weight: 800; border-radius: 10px; display: flex; align-items: center; gap: 6px; width: 100%; justify-content: center; transition: all 0.2s;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                                <span>Settings</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Chores Content -->
        <div class="kids-main-grid">
            
            <!-- Pending Chores -->
            <div class="card">
                <div class="card-header">
                    <h3 style="font-weight: 900;">Pending Chores</h3>
                    <span class="badge" style="background: var(--warning-soft); color: var(--warning);">{{ $chores->count() }} Tasks</span>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="eco-grid-table">
                        <div class="eco-grid-header chores-grid">
                            <div>Chore / Reward</div>
                            <div style="text-align: right;">Action</div>
                        </div>
                        <div class="eco-grid-body">
                            @forelse($chores as $chore)
                                <div class="eco-grid-row chores-grid" style="padding: 12px 16px;">
                                    <div>
                                        <div style="font-weight: 800; color: var(--text-main);">{{ $chore->title }}</div>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                                            <div style="font-size: 11px; font-weight: 900; color: var(--primary);">+{{ $chore->score }} Points</div>
                                            <span class="badge badge-soft" style="font-size: 9px; padding: 1px 6px;">{{ $chore->user->name }}</span>
                                        </div>
                                        @if($chore->description)
                                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 6px;">{{ $chore->description }}</div>
                                        @endif
                                    </div>

                                    <div style="display: flex; align-items: center; gap: 12px; justify-content: flex-end;">
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

                                        @if(!auth()->user()->is_child)
                                            <button wire:click="deleteChore({{ $chore->id }})" 
                                                    wire:confirm="Are you sure you want to delete this chore?"
                                                    class="eco-delete-btn" style="opacity: 0.5;">
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
                                    <div class="eco-grid-row finished-grid" style="padding: 12px 16px;">
                                        <div>
                                            <div style="font-weight: 700; color: var(--text-muted); text-decoration: line-through;">{{ $chore->title }}</div>
                                            <div style="display: flex; gap: 8px; align-items: center; margin-top: 4px;">
                                                <div style="font-size: 11px; font-weight: 800; color: var(--success);">Earned {{ $chore->score }} Points</div>
                                                <span class="badge badge-soft mobile-only" style="font-size: 9px; padding: 1px 6px;">{{ $chore->user->name }}</span>
                                            </div>
                                            <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">{{ $chore->completed_at ? $chore->completed_at->diffForHumans() : '' }} <span class="desktop-only">• By {{ $chore->user->name }}</span></div>
                                        </div>
                                        <div class="desktop-only" style="text-align: center;">
                                            <div style="padding: 4px 10px; border-radius: 8px; background: var(--success-soft); color: var(--success); font-size: 9px; font-weight: 900; text-transform: uppercase; display: inline-block;">Finished</div>
                                        </div>
                                        <div style="text-align: right; display: flex; justify-content: flex-end;">
                                            @if(!auth()->user()->is_child)
                                                <button wire:click="revertChore({{ $chore->id }})" 
                                                        wire:confirm="Are you sure you want to revert this chore? The points earned will be deducted from the child's balance."
                                                        class="btn" style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-muted); padding: 6px 14px; font-size: 11px; font-weight: 700; white-space: nowrap; display: flex; align-items: center; gap: 6px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                                    REVERT
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
                                    <div class="eco-grid-row redemption-grid" style="padding: 12px 16px;">
                                        <div>
                                            <div style="font-weight: 800; color: var(--text-main);">{{ $redemption->description }}</div>
                                            <div style="display: flex; gap: 8px; align-items: center; margin-top: 4px;">
                                                <div style="font-size: 11px; font-weight: 900; color: var(--danger);">Spent {{ $redemption->score }} Points</div>
                                                <span class="badge badge-soft mobile-only" style="font-size: 9px; padding: 1px 6px;">{{ $redemption->user->name }}</span>
                                            </div>
                                            <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">{{ $redemption->created_at->diffForHumans() }} <span class="desktop-only">• Used by {{ $redemption->user->name }}</span></div>
                                        </div>
                                        <div class="desktop-only" style="text-align: center;">
                                            <div style="padding: 4px 10px; border-radius: 8px; background: var(--danger-soft); color: var(--danger); font-size: 9px; font-weight: 900; text-transform: uppercase; display: inline-block;">Used</div>
                                        </div>
                                        <div style="display: flex; justify-content: flex-end;">
                                            @if(!auth()->user()->is_child)
                                                <button wire:click="deleteRedemption({{ $redemption->id }})" 
                                                        wire:confirm="Are you sure you want to delete this redemption? The points will be refunded to the child's balance."
                                                        class="eco-delete-btn" style="opacity: 0.4;">
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
            <div class="modal-compact-header" style="padding: 24px 32px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.01);">
                <h3 style="font-size: 1.5rem; font-weight: 900; color: var(--text-main);">Assign Chore</h3>
                <button @click="$wire.set('showAddChoreModal', false)" style="background: var(--bg-input); border: none; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 20px;">×</button>
            </div>
            
            <form wire:submit.prevent="addChore" class="modal-compact-form" style="padding: 32px; display: flex; flex-direction: column; gap: 24px;">
                @if($templates->count() > 0)
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 12px;">Quick Templates</label>
                    <div class="flex-wrap-mobile" style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px; scrollbar-width: none;">
                        @foreach($templates as $template)
                            <button type="button" wire:click="applyTemplate({{ $template->id }})" class="template-btn-mobile" style="flex-shrink: 0; background: var(--bg-card); border: 1px solid var(--border-color); padding: 8px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; color: var(--text-main); cursor: pointer; transition: all 0.2s; white-space: nowrap;">
                                {{ $template->title }} ({{ $template->score }} pts)
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Chore Title</label>
                    <input type="text" wire:model="title" style="background: var(--bg-input); color: var(--text-main); border: 2px solid var(--border-color); padding: 14px 18px; border-radius: 16px; width: 100%; font-size: 15px; font-weight: 600; outline: none; transition: border-color 0.2s;" placeholder="E.g. Vacuum the living room" autofocus>
                    @error('title') <p style="font-size: 11px; color: var(--danger); margin-top: 6px; font-weight: 700;">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="compact-label" style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Description (Optional)</label>
                    <textarea wire:model="description" class="compact-textarea" style="background: var(--bg-input); color: var(--text-main); border: 2px solid var(--border-color); padding: 14px 18px; border-radius: 16px; width: 100%; font-size: 15px; font-weight: 600; outline: none; min-height: 100px; resize: vertical;" placeholder="Add some details..."></textarea>
                </div>

                <div class="mobile-stack-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
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

                <div style="margin-top: 8px;">
                    <label class="compact-checkbox-box" style="display: flex; align-items: center; gap: 12px; cursor: pointer; background: var(--bg-input); padding: 14px 18px; border-radius: 16px; border: 2px solid var(--border-color); transition: all 0.2s;">
                        <input type="checkbox" wire:model="complete_immediately" style="width: 20px; height: 20px; accent-color: var(--success); cursor: pointer;">
                        <div>
                            <div style="font-size: 14px; font-weight: 800; color: var(--text-main);">Mark as completed immediately</div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Points will be awarded and history recorded instantly.</div>
                        </div>
                    </label>
                </div>

                <div class="compact-footer" style="display: flex; gap: 12px; margin-top: 12px;">
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
            <div class="modal-compact-header" style="padding: 24px 32px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 1.25rem; font-weight: 900; color: var(--text-main);">Adjust Points</h3>
                <button @click="$wire.set('showAdjustPointsModal', false)" style="background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 24px;">×</button>
            </div>
            
            <div class="modal-compact-form" style="padding: 32px; display: flex; flex-direction: column; gap: 24px;">
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

                <div class="compact-footer" style="display: flex; gap: 12px;">
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
            <div class="modal-compact-header" style="padding: 24px 32px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: rgba(37, 99, 235, 0.05);">
                <div>
                    <h3 style="font-size: 1.5rem; font-weight: 900; color: var(--primary);">Use Points</h3>
                    <p style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px;">Redeem points for rewards</p>
                </div>
                <button @click="$wire.set('showUsePointsModal', false)" style="background: white; border: 1px solid var(--border-color); width: 36px; height: 36px; border-radius: 50%; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 20px;">×</button>
            </div>
            
            <form wire:submit.prevent="usePoints" class="modal-compact-form" style="padding: 32px; display: flex; flex-direction: column; gap: 24px;">
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

                <div class="compact-footer" style="display: flex; gap: 12px; margin-top: 12px;">
                    <button type="button" @click="$wire.set('showUsePointsModal', false)" class="btn" style="flex: 1; background: var(--bg-input); color: var(--text-main); border: 1px solid var(--border-color); padding: 16px; border-radius: 18px;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex: 2; padding: 16px; border-radius: 18px; font-size: 16px;">Confirm Redemption</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Manage Templates Modal -->
    @if($showManageTemplatesModal)
    <div class="modal-overlay" @click.self="$wire.set('showManageTemplatesModal', false)">
        <div class="modal-content animate-in templates-grid" style="max-width: 800px; text-align: left; padding: 0;">
            <!-- Left Side: Template List -->
            <div style="padding: 32px; border-right: 1px solid var(--border-color); background: rgba(0,0,0,0.01);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="font-size: 1.25rem; font-weight: 900; color: var(--text-main);">Chore Library</h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px; max-height: 480px; overflow-y: auto; padding-right: 8px;">
                    @forelse($templates as $template)
                        <div style="background: var(--bg-card); border: 1px solid @if($editingTemplateId == $template->id) var(--primary) @else var(--border-color) @endif; border-radius: 16px; padding: 16px; transition: all 0.2s; position: relative; @if($editingTemplateId == $template->id) box-shadow: 0 0 0 2px var(--primary-soft); @endif">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div style="flex: 1; margin-right: 12px;">
                                    <div style="font-weight: 800; font-size: 14px; color: var(--text-main);">{{ $template->title }}</div>
                                    <div style="font-size: 11px; font-weight: 900; color: var(--primary); margin-top: 2px;">{{ $template->score }} Points</div>
                                    @if($template->recurrence_type && $template->recurrence_type !== 'none')
                                        <div style="font-size: 9px; font-weight: 900; color: var(--success); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                            @if($template->recurrence_type == 'weekly' && is_array($template->recurrence_day))
                                                Every {{ implode(', ', array_map(fn($d) => substr($d, 0, 3), $template->recurrence_day)) }}
                                            @else
                                                {{ ucfirst($template->recurrence_type) }} @if($template->recurrence_day) ({{ $template->recurrence_day }}) @endif
                                            @endif
                                        </div>
                                    @endif
                                    @php $assignedUsers = $template->assignedUsers(); @endphp
                                    @if($assignedUsers->count() > 0)
                                        <div style="font-size: 9px; font-weight: 800; color: var(--text-muted); margin-top: 2px;">Assigned to {{ $assignedUsers->pluck('name')->implode(', ') }}</div>
                                    @endif
                                    @if($template->description)
                                        <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $template->description }}</div>
                                    @endif
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <button wire:click="editTemplate({{ $template->id }})" style="background: var(--bg-input); border: none; color: var(--text-muted); cursor: pointer; padding: 6px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <button wire:click="deleteTemplate({{ $template->id }})" style="background: var(--danger-soft); border: none; color: var(--danger); cursor: pointer; padding: 6px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted); border: 2px dashed var(--border-color); border-radius: 20px;">
                            <div style="font-size: 13px; font-weight: 800; color: var(--text-muted);">No templates yet</div>
                            <p style="font-size: 11px; margin-top: 4px;">Create your first chore template on the right.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right Side: Editor -->
            <div style="padding: 32px; position: relative;">
                <button @click="$wire.set('showManageTemplatesModal', false)" style="position: absolute; right: 24px; top: 24px; background: var(--bg-input); border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 20px;">×</button>
                
                <div class="modal-compact-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h3 style="font-size: 1.25rem; font-weight: 900; color: var(--text-main);">
                        {{ $editingTemplateId ? 'Edit Template' : 'Create Template' }}
                    </h3>
                </div>

                <form wire:submit.prevent="saveTemplate" class="modal-compact-form" style="display: flex; flex-direction: column; gap: 24px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Template Title</label>
                        <input type="text" wire:model.defer="templateTitle" style="background: var(--bg-input); color: var(--text-main); border: 2px solid var(--border-color); padding: 14px 18px; border-radius: 12px; width: 100%; font-size: 15px; font-weight: 700; outline: none;" placeholder="e.g. Empty Dishwasher">
                        @error('templateTitle') <p style="font-size: 11px; color: var(--danger); margin-top: 6px; font-weight: 700;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="compact-label" style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Default Description</label>
                        <textarea wire:model.defer="templateDescription" class="compact-textarea" style="background: var(--bg-input); color: var(--text-main); border: 2px solid var(--border-color); padding: 14px 18px; border-radius: 12px; width: 100%; font-size: 15px; font-weight: 700; outline: none; min-height: 100px; resize: vertical;" placeholder="Optional details..."></textarea>
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 12px;">Assigned To (Required)</label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px;">
                            @foreach(\App\Models\User::where('is_child', true)->get() as $child)
                                <div wire:click="toggleChildSelection({{ $child->id }})" style="cursor: pointer; padding: 12px; border-radius: 12px; border: 2px solid @if(in_array($child->id, $templateAssignedUserIds)) var(--primary) @else var(--border-color) @endif; background: @if(in_array($child->id, $templateAssignedUserIds)) var(--primary-soft) @else var(--bg-card) @endif; transition: all 0.2s; text-align: center;">
                                    <div style="font-size: 12px; font-weight: 800; color: @if(in_array($child->id, $templateAssignedUserIds)) var(--primary) @else var(--text-main) @endif;">{{ $child->name }}</div>
                                </div>
                            @endforeach
                        </div>
                        @error('templateAssignedUserIds') <p style="font-size: 11px; color: var(--danger); margin-top: 6px; font-weight: 700;">At least one child must be assigned.</p> @enderror
                    </div>

                    <div class="mobile-stack-grid" style="display: grid; grid-template-columns: 1fr; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Default Points</label>
                            <div style="position: relative;">
                                <input type="number" wire:model.defer="templateScore" style="background: var(--bg-input); color: var(--text-main); border: 2px solid var(--border-color); padding: 14px 44px 14px 18px; border-radius: 12px; width: 100%; font-size: 18px; font-weight: 900; outline: none;">
                                <span style="position: absolute; right: 18px; top: 50%; transform: translateY(-50%); font-size: 12px; font-weight: 900; color: var(--primary);">PTS</span>
                            </div>
                        </div>
                    </div>

                    <div style="background: var(--bg-input); border-radius: 16px; padding: 20px; border: 1px dashed var(--border-color);">
                        <div style="font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            Recurrence Strategy
                        </div>
                        
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <div wire:click="$set('templateRecurrenceType', 'none')" style="flex: 1; min-width: 100px; cursor: pointer; text-align: center;">
                                <div style="padding: 10px; border-radius: 12px; border: 2px solid @if($templateRecurrenceType == 'none') var(--primary) @else var(--border-color) @endif; background: @if($templateRecurrenceType == 'none') var(--primary-soft) @else var(--bg-card) @endif; transition: all 0.2s;">
                                    <div style="font-size: 12px; font-weight: 900; color: @if($templateRecurrenceType == 'none') var(--primary) @else var(--text-muted) @endif;">None</div>
                                </div>
                            </div>
                            <div wire:click="$set('templateRecurrenceType', 'daily')" style="flex: 1; min-width: 100px; cursor: pointer; text-align: center;">
                                <div style="padding: 10px; border-radius: 12px; border: 2px solid @if($templateRecurrenceType == 'daily') var(--primary) @else var(--border-color) @endif; background: @if($templateRecurrenceType == 'daily') var(--primary-soft) @else var(--bg-card) @endif; transition: all 0.2s;">
                                    <div style="font-size: 12px; font-weight: 900; color: @if($templateRecurrenceType == 'daily') var(--primary) @else var(--text-muted) @endif;">Daily</div>
                                </div>
                            </div>
                            <div wire:click="$set('templateRecurrenceType', 'weekly')" style="flex: 1; min-width: 100px; cursor: pointer; text-align: center;">
                                <div style="padding: 10px; border-radius: 12px; border: 2px solid @if($templateRecurrenceType == 'weekly') var(--primary) @else var(--border-color) @endif; background: @if($templateRecurrenceType == 'weekly') var(--primary-soft) @else var(--bg-card) @endif; transition: all 0.2s;">
                                    <div style="font-size: 12px; font-weight: 900; color: @if($templateRecurrenceType == 'weekly') var(--primary) @else var(--text-muted) @endif;">Weekly</div>
                                </div>
                            </div>
                            <div wire:click="$set('templateRecurrenceType', 'monthly')" style="flex: 1; min-width: 100px; cursor: pointer; text-align: center;">
                                <div style="padding: 10px; border-radius: 12px; border: 2px solid @if($templateRecurrenceType == 'monthly') var(--primary) @else var(--border-color) @endif; background: @if($templateRecurrenceType == 'monthly') var(--primary-soft) @else var(--bg-card) @endif; transition: all 0.2s;">
                                    <div style="font-size: 12px; font-weight: 900; color: @if($templateRecurrenceType == 'monthly') var(--primary) @else var(--text-muted) @endif;">Monthly</div>
                                </div>
                            </div>
                        </div>

                        @if($templateRecurrenceType == 'weekly')
                        <div style="margin-top: 16px; border-top: 1px solid var(--border-color); padding-top: 16px;">
                            <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; margin-bottom: 12px;">Occurs on every:</label>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                    @php $shortDay = substr($day, 0, 3); @endphp
                                    <button type="button" wire:click="toggleRecurrenceDay('{{ $day }}')" 
                                        style="flex: 1; min-width: 44px; padding: 10px 5px; border-radius: 10px; border: 2px solid @if(in_array($day, $templateRecurrenceDay ?? [])) var(--primary) @else var(--border-color) @endif; background: @if(in_array($day, $templateRecurrenceDay ?? [])) var(--primary-soft) @else var(--bg-card) @endif; color: @if(in_array($day, $templateRecurrenceDay ?? [])) var(--primary) @else var(--text-muted) @endif; font-size: 11px; font-weight: 900; cursor: pointer; transition: all 0.2s;">
                                        {{ $shortDay }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($templateRecurrenceType == 'monthly')
                        <div style="margin-top: 16px; border-top: 1px solid var(--border-color); padding-top: 16px;">
                            <label style="display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">Occurs on day of month:</label>
                            <input type="number" wire:model.defer="templateRecurrenceDay" min="1" max="31" style="background: var(--bg-card); color: var(--text-main); border: 2px solid var(--border-color); padding: 10px 14px; border-radius: 10px; width: 100%; font-size: 14px; font-weight: 800; outline: none;" placeholder="1-31">
                        </div>
                        @endif
                    </div>

                    <div class="compact-footer" style="display: flex; gap: 12px; margin-top: 8px;">
                        <button type="submit" class="btn btn-primary" style="flex: 1; padding: 16px; border-radius: 18px; font-weight: 900; font-size: 16px;">
                            {{ $editingTemplateId ? 'Update Template' : 'Save as Template' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    <!-- Quick Assign Modal -->
    @if($showQuickAssignModal)
    <div class="modal-overlay" @click.self="$wire.set('showQuickAssignModal', false)">
        <div class="modal-content animate-in" style="max-width: 500px; text-align: left; padding: 0;">
            <div style="padding: 24px 32px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: rgba(22, 163, 74, 0.05);">
                <div>
                    <h3 style="font-size: 1.5rem; font-weight: 900; color: var(--success);">Quick Assign</h3>
                    <p style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px;">Assign to {{ $quickAssignUserName }}</p>
                </div>
                <button @click="$wire.set('showQuickAssignModal', false)" style="background: white; border: 1px solid var(--border-color); width: 36px; height: 36px; border-radius: 50%; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 20px;">×</button>
            </div>
            
            <div style="padding: 32px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 12px 20px; background: var(--bg-input); border-radius: 16px; border: 1px solid var(--border-color);">
                    <div style="font-size: 13px; font-weight: 800; color: var(--text-main);">Mark as completed immediately</div>
                    <label class="switch">
                        <input type="checkbox" wire:model="quickAssignCompleteImmediately">
                        <span class="slider round"></span>
                    </label>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px; max-height: 400px; overflow-y: auto; padding: 4px; margin: -4px;">
                    @forelse($templates as $template)
                        <button wire:click="quickAssignFromTemplate({{ $template->id }})" class="template-card-btn" style="text-align: left; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 16px; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; width: 100%; position: relative;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 900; font-size: 15px; color: var(--text-main);">{{ $template->title }}</div>
                                    <div style="font-size: 12px; font-weight: 900; color: var(--primary); margin-top: 2px;">+{{ $template->score }} Points</div>
                                </div>
                                <div style="background: var(--bg-input); color: var(--primary); width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                </div>
                            </div>
                        </button>
                    @empty
                        <div style="text-align: center; padding: 40px 20px; color: var(--text-muted); border: 2px dashed var(--border-color); border-radius: 20px;">
                            <p style="font-size: 13px; font-weight: 800;">No templates found</p>
                            <button wire:click="openManageTemplatesModal" class="btn" style="color: var(--primary); font-size: 12px; font-weight: 800; margin-top: 8px;">Create Templates</button>
                        </div>
                    @endforelse
                </div>

                <div style="margin-top: 24px;">
                    <button @click="$wire.set('showQuickAssignModal', false)" class="btn" style="width: 100%; background: var(--bg-input); color: var(--text-muted); border: 1px solid var(--border-color); padding: 14px; border-radius: 16px; font-weight: 800;">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .template-card-btn:hover {
            border-color: var(--primary) !important;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            z-index: 10;
        }
        .template-card-btn:active {
            transform: translateY(0);
        }
    </style>
    @endif
</div>
