<div class="animate-in">
    <!-- Header -->
    <div class="flex-header">
        <div style="min-width: 280px;">
            <div class="badge badge-soft" style="color: var(--primary); background: var(--primary-soft); margin-bottom: var(--space-2);">Overview</div>
            <h2 style="font-size: clamp(1.5rem, 5vw, 2.5rem); font-weight: 900; letter-spacing: -0.02em; line-height: 1;">Dashboard</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-top: var(--space-1);">Global management of your household ecosystems</p>
        </div>
        <div style="text-align: right; flex-shrink: 0;">
            <div style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Current Session</div>
            <div style="font-size: 14px; font-weight: 700;">{{ now()->format('l, j F') }}</div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="summary-grid">
        @if($economyEnabled)
        <div class="summary-card accent">
            <div class="summary-label" style="color: rgba(255,255,255,0.6);">Current Months Income</div>
            <div class="summary-value">{{ number_format($totalIncome, 0, ',', ' ') }} <span style="font-size: 0.5em; opacity: 0.6;">KR</span></div>
        </div>
        @endif
        @if($economyEnabled)
        <div class="summary-card">
            <div class="summary-label">Current  Months Expenses</div>
            <div class="summary-value" style="color: var(--danger);">{{ number_format($totalExpenses, 0, ',', ' ') }} <span style="font-size: 0.5em; opacity: 1;">KR</span></div>
        </div>
        @endif
        @if($economyEnabled)
        <div class="summary-card">
            <div class="summary-label">Total Savings</div>
            <div class="summary-value" style="color: var(--primary);">{{ number_format($totalSavings, 0, ',', ' ') }} <span style="font-size: 0.5em; opacity: 1;">KR</span></div>
            <div style="font-size: 11px; margin-top: var(--space-3); color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary);"></div>
                Accumulated Capital
            </div>
        </div>
        @endif
        @if($shoppingEnabled)
        <div class="summary-card">
            <div class="summary-label">Shopping Lists</div>
            <div class="summary-value" style="color: var(--text-main);">{{ $shoppingItemsCount }} <span style="font-size: 0.5em; opacity: 1;">Items</span></div>
            <div style="font-size: 11px; margin-top: var(--space-3); color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                Waiting to be bought
            </div>
        </div>
        @endif
        @if($todoEnabled)
        <div class="summary-card" @if($todoItemsOverdue > 0) style="border-color: var(--danger-soft);" @endif>
            <div class="summary-label">Todo Tasks</div>
            <div class="summary-value" style="color: var(--text-main);">
                {{ $todoItemsWaiting }} <span style="font-size: 0.5em; opacity: 1;">Tasks</span>
            </div>
            <div style="font-size: 11px; margin-top: var(--space-3); color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                @if($todoItemsOverdue > 0)
                    <div style="padding: 2px 6px; border-radius: 4px; background: var(--danger-soft); color: var(--danger); font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        {{ $todoItemsOverdue }} overdue
                    </div>
                @else
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--success);"></div>
                    All caught up
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 400px), 1fr)); gap: var(--space-8);">
        @if($todoEnabled)
        <!-- Task Productivity Chart -->
        <div class="card" style="padding: 0; overflow: hidden; border-radius: 28px;">
            <div style="padding: var(--space-6) var(--space-8); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.01);">
                <div>
                    <h3 style="font-size: 18px; font-weight: 900;">Task Productivity</h3>
                    <p style="font-size: 11px; color: var(--text-muted);">Completed tasks over the last 3 months</p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 18px; font-weight: 900; color: var(--success);">{{ $totalCompleted }}</div>
                    <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Total Done</div>
                </div>
            </div>
            <div style="padding: var(--space-8) 0 0 0; height: 320px; background: var(--bg-input); display: flex; flex-direction: column; position: relative;">
                @php
                    $maxCount = max(array_column($chartPoints, 'count')) ?: 1;
                    $width = 1000;
                    $height = 200;
                    $pointCount = count($chartPoints);
                    $stepX = $pointCount > 1 ? $width / ($pointCount - 1) : 0;
                    
                    $path = "";
                    foreach($chartPoints as $i => $point) {
                        $x = $i * $stepX;
                        // Invert Y because SVG coordinates start from top
                        $y = $height - ($point['count'] / $maxCount * $height);
                        $path .= ($i === 0 ? "M" : " L") . " $x,$y";
                    }
                    $fillPath = $path . " L $width,$height L 0,$height Z";
                    
                    // Generate month labels
                    $months = [];
                    foreach($chartPoints as $i => $point) {
                        $d = \Carbon\Carbon::parse($point['date']);
                        if ($d->day === 1) {
                            $months[] = [
                                'label' => $d->format('M'),
                                'x' => ($i / ($pointCount - 1)) * 100
                            ];
                        }
                    }
                @endphp
                
                <div style="flex: 1; position: relative; padding: 0 var(--space-12);">
                    <svg viewBox="0 0 {{ $width }} {{ $height }}" preserveAspectRatio="none" style="width: 100%; height: 100%; display: block; overflow: visible;">
                        <defs>
                            <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="var(--success)" stop-opacity="0.3" />
                                <stop offset="100%" stop-color="var(--success)" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        
                        <!-- Fill AREA -->
                        <path d="{{ $fillPath }}" fill="url(#chartGradient)" />
                        
                        <!-- LINE -->
                        <path d="{{ $path }}" fill="none" stroke="var(--success)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="filter: drop-shadow(0 4px 6px rgba(22, 163, 74, 0.2));" />
                    </svg>
                </div>
                
                <!-- X-Axis Labels -->
                <div style="height: 40px; border-top: 1px solid var(--border-color); margin-top: var(--space-4); padding: 0 var(--space-12);">
                    <div style="position: relative; width: 100%; height: 100%;">
                        @foreach($months as $index => $month)
                            <div style="position: absolute; left: {{ $month['x'] }}%; transform: {{ $index === 0 ? 'none' : ($index === count($months) - 1 ? 'translateX(-100%)' : 'translateX(-50%)') }}; top: 10px; font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; white-space: nowrap;">
                                {{ $month['label'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($kidsEnabled)
        <!-- Kids System Summary -->
        <div class="card" style="padding: 0; overflow: hidden; border-radius: 28px;">
            <div style="padding: var(--space-6) var(--space-8); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.01);">
                <div>
                    <h3 style="font-size: 18px; font-weight: 900;">Kids Points Overview</h3>
                    <p style="font-size: 11px; color: var(--text-muted);">Click a child to quick-assign points</p>
                </div>
                <div style="text-align: right;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
            </div>
            <div style="padding: var(--space-6) var(--space-8); background: var(--bg-input); min-height: 200px;">
                @if($children->count() > 0)
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        @foreach($children as $child)
                            <div wire:click="openQuickAssignModal({{ $child->id }})" class="dashboard-child-box" style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 22px 16px; border-radius: 20px; cursor: pointer; transition: all 0.2s; position: relative; overflow: hidden; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px; box-shadow: var(--shadow-sm);">
                                <div style="width: 48px; height: 48px; border-radius: 16px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 900; box-shadow: 0 8px 16px -4px var(--primary-soft);">
                                    {{ strtoupper(substr($child->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-size: 14px; font-weight: 900; color: var(--text-main);">{{ $child->name }}</div>
                                    <div style="font-size: 1.5rem; font-weight: 900; color: var(--primary); margin: 4px 0;">{{ $child->accumulated_score }} <span style="font-size: 10px; opacity: 0.7;">PTS</span></div>
                                    <div style="font-size: 11px; font-weight: 800; color: var(--success); display: flex; align-items: center; justify-content: center; gap: 4px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                                        {{ $child->monthly_points }} monthly
                                    </div>
                                </div>
                                
                                <div class="hover-assign-overlay" style="position: absolute; inset: 0; background: var(--success); color: white; display: flex; align-items: center; justify-content: center; opacity: 0; transition: all 0.2s; transform: translateY(100%);">
                                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 900; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                                        Quick Assign
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 160px; color: var(--text-muted); text-align: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.3; margin-bottom: 12px;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <div style="font-size: 13px; font-weight: 800;">No children registered</div>
                        <p style="font-size: 11px; margin-top: 4px;">Tag users as children in the <a href="{{ route('admin.users') }}" style="color: var(--primary); font-weight: 800; text-decoration: underline;">Admin panel</a></p>
                    </div>
                @endif
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
                            <a href="{{ route('kids') }}" class="btn" style="color: var(--primary); font-size: 12px; font-weight: 800; margin-top: 8px;">Go to Kids System</a>
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
        .dashboard-child-box:hover {
            border-color: var(--success) !important;
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        .dashboard-child-box:hover .hover-assign-overlay {
            opacity: 1;
            transform: translateY(0);
        }
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
