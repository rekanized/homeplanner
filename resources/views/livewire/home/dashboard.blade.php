<div class="animate-in">
    <!-- Header -->
    <div class="flex-header">
        <div style="min-width: 280px;">
            <div class="badge badge-soft" style="color: var(--primary); background: var(--primary-soft); margin-bottom: var(--space-2);">{{ __('Overview') }}</div>
            <h2 style="font-size: clamp(1.5rem, 5vw, 2.5rem); font-weight: 900; letter-spacing: -0.02em; line-height: 1;">{{ __('Dashboard') }}</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-top: var(--space-1);">{{ __('Global management of your household ecosystems') }}</p>
        </div>
        <div style="text-align: right; flex-shrink: 0;">
            <div style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">{{ __('Current Session') }}</div>
            <div style="font-size: 14px; font-weight: 700;">{{ now()->translatedFormat('l, j F') }}</div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="summary-grid">
        @if($economyEnabled)
        <div class="summary-card accent">
            <div class="summary-label" style="color: rgba(255,255,255,0.6);">{{ __('Current Months Income') }}</div>
            <div class="summary-value">{{ number_format($totalIncome, 0, ',', ' ') }} <span style="font-size: 0.5em; opacity: 0.6;">{{ __("kr") }}</span></div>
        </div>
        @endif
        @if($economyEnabled)
        <div class="summary-card">
            <div class="summary-label">{{ __('Current Months Expenses') }}</div>
            <div class="summary-value" style="color: var(--danger);">{{ number_format($totalExpenses, 0, ',', ' ') }} <span style="font-size: 0.5em; opacity: 1;">{{ __("kr") }}</span></div>
        </div>
        @endif
        @if($economyEnabled)
        <div class="summary-card">
            <div class="summary-label">{{ __('Total Savings') }}</div>
            <div class="summary-value" style="color: var(--primary);">{{ number_format($totalSavings, 0, ',', ' ') }} <span style="font-size: 0.5em; opacity: 1;">{{ __("kr") }}</span></div>
            <div style="font-size: 11px; margin-top: var(--space-3); color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary);"></div>
                {{ __('Accumulated Capital') }}
            </div>
        </div>
        @endif
        @if($shoppingEnabled)
        <div class="summary-card">
            <div class="summary-label">{{ __('Shopping Lists') }}</div>
            <div class="summary-value" style="color: var(--text-main);">{{ $shoppingItemsCount }} <span style="font-size: 0.5em; opacity: 1;">{{ __('Items') }}</span></div>
            <div style="font-size: 11px; margin-top: var(--space-3); color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                {{ __('Waiting to be bought') }}
            </div>
        </div>
        @endif
        @if($todoEnabled)
        <div class="summary-card" @if($todoItemsOverdue > 0) style="border-color: var(--danger-soft);" @endif>
            <div class="summary-label">{{ __('Todo Tasks') }}</div>
            <div class="summary-value" style="color: var(--text-main);">
                {{ $todoItemsWaiting }} <span style="font-size: 0.5em; opacity: 1;">{{ __('Tasks') }}</span>
            </div>
            <div style="font-size: 11px; margin-top: var(--space-3); color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                @if($todoItemsOverdue > 0)
                    <div style="padding: 2px 6px; border-radius: 4px; background: var(--danger-soft); color: var(--danger); font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        {{ $todoItemsOverdue }} {{ __('overdue') }}
                    </div>
                @else
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--success);"></div>
                    {{ __('All caught up') }}
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
            <div class="task-productivity-header" style="padding: var(--space-6) var(--space-8); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: linear-gradient(180deg, rgba(37, 99, 235, 0.04) 0%, rgba(37, 99, 235, 0) 100%); gap: 20px; flex-wrap: wrap;">
                <div>
                    <h3 style="font-size: 18px; font-weight: 900;">{{ __('Task Productivity') }}</h3>
                    <p style="font-size: 11px; color: var(--text-muted);">{{ __('Completed tasks over the last 3 months') }}</p>
                </div>
                <div class="task-productivity-summary" style="display: flex; align-items: stretch; gap: 10px; flex-wrap: wrap; justify-content: flex-end; margin-left: auto;">
                    <div class="task-productivity-pill" style="min-width: 88px; padding: 10px 12px; border-radius: 16px; background: var(--bg-card); border: 1px solid var(--border-color); text-align: right; box-shadow: var(--shadow-sm);">
                        <div style="font-size: 18px; font-weight: 900; color: var(--success); line-height: 1;">{{ $totalCompleted }}</div>
                        <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-top: 6px;">{{ __('Total Done') }}</div>
                    </div>
                    <div class="task-productivity-pill" style="min-width: 88px; padding: 10px 12px; border-radius: 16px; background: var(--bg-card); border: 1px solid var(--border-color); text-align: right; box-shadow: var(--shadow-sm);">
                        <div style="font-size: 18px; font-weight: 900; color: var(--primary); line-height: 1;">{{ $maxWeeklyCompleted }}</div>
                        <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-top: 6px;">{{ __('Peak Week') }}</div>
                    </div>
                    <div class="task-productivity-pill" style="min-width: 88px; padding: 10px 12px; border-radius: 16px; background: var(--bg-card); border: 1px solid var(--border-color); text-align: right; box-shadow: var(--shadow-sm);">
                        <div style="font-size: 18px; font-weight: 900; color: var(--text-main); line-height: 1;">{{ $averageWeeklyCompleted }}</div>
                        <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-top: 6px;">{{ __('Avg / Week') }}</div>
                    </div>
                    <div class="task-productivity-tag" style="align-self: center; padding: 8px 12px; border-radius: 999px; background: rgba(22, 163, 74, 0.08); color: var(--success); font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.08em;">
                        {{ __('Weekly View') }}
                    </div>
                </div>
            </div>
            <div class="task-productivity-body" style="padding: var(--space-8); min-height: 320px; background: linear-gradient(180deg, var(--bg-card) 0%, var(--bg-input) 100%); display: flex; flex-direction: column; gap: 18px; position: relative;">
                <div class="task-productivity-chart-shell" style="display: grid; grid-template-columns: 44px 1fr; gap: 14px; flex: 1; min-height: 0;">
                    <div class="task-productivity-scale" style="display: flex; flex-direction: column; justify-content: space-between; align-items: flex-start; padding: 6px 0 2px; color: var(--text-muted); font-size: 10px; font-weight: 800; text-transform: uppercase;">
                        <span>{{ $maxWeeklyCompleted }}</span>
                        <span>{{ max(1, (int) ceil($maxWeeklyCompleted / 2)) }}</span>
                        <span>0</span>
                    </div>

                    <div class="task-productivity-frame">
                        <div class="task-productivity-plot" style="position: relative; min-height: 220px; border-radius: 24px; border: 1px solid rgba(148, 163, 184, 0.18); background: linear-gradient(180deg, var(--bg-card) 0%, var(--bg-input) 100%); padding: 18px 18px 14px; overflow: hidden;">
                        <div class="task-productivity-grid" style="position: absolute; inset: 18px 18px 18px; display: grid; grid-template-rows: repeat(3, 1fr); pointer-events: none;">
                            <div style="border-top: 1px dashed rgba(148, 163, 184, 0.35);"></div>
                            <div style="border-top: 1px dashed rgba(148, 163, 184, 0.28);"></div>
                            <div style="border-top: 1px dashed rgba(148, 163, 184, 0.22);"></div>
                        </div>

                        <div class="task-productivity-bars" style="position: relative; z-index: 1; height: 100%; display: grid; grid-template-columns: repeat({{ max($weeklyProductivity->count(), 1) }}, minmax(0, 1fr)); gap: 10px; align-items: end; min-width: 0; width: 100%;">
                            @foreach($weeklyProductivity as $week)
                                <div class="task-productivity-week" style="height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; gap: 10px; min-width: 0;">
                                    <div class="task-productivity-value" style="font-size: 11px; font-weight: 900; color: {{ $week['count'] > 0 ? 'var(--success)' : 'var(--text-muted)' }}; min-height: 16px; line-height: 1;">{{ $week['count'] > 0 ? $week['count'] : '' }}</div>
                                    <div class="task-productivity-bar-track" style="position: relative; width: 100%; flex: 1; display: flex; align-items: end;">
                                        <div style="position: absolute; inset: 0; border-radius: 18px 18px 10px 10px; background: rgba(148, 163, 184, 0.08);"></div>
                                        <div style="position: relative; width: 100%; height: {{ $week['height_percent'] }}%; min-height: {{ $week['count'] > 0 ? '16px' : '4px' }}; border-radius: 18px 18px 10px 10px; background: {{ $week['is_current_week'] ? 'linear-gradient(180deg, rgba(59, 130, 246, 0.9) 0%, rgba(37, 99, 235, 1) 100%)' : 'linear-gradient(180deg, rgba(74, 222, 128, 0.92) 0%, rgba(22, 163, 74, 1) 100%)' }}; box-shadow: {{ $week['count'] > 0 ? ($week['is_current_week'] ? '0 12px 24px -18px rgba(37, 99, 235, 0.9)' : '0 12px 24px -18px rgba(22, 163, 74, 0.95)') : 'none' }}; border: 1px solid {{ $week['is_current_week'] ? 'rgba(37, 99, 235, 0.28)' : 'rgba(22, 163, 74, 0.16)' }};"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @php
                        $axisIndices = collect([0, (int) floor((max($weeklyProductivity->count(), 1) - 1) / 2), max($weeklyProductivity->count(), 1) - 1])
                            ->unique()
                            ->values();
                    @endphp
                    <div class="task-productivity-axis" style="position: relative; height: 28px; margin-top: 10px; min-width: 0; width: 100%;">
                        @foreach($axisIndices as $axisIndex)
                            @php
                                $axisWeek = $weeklyProductivity[$axisIndex];
                                $leftPercent = $weeklyProductivity->count() > 1
                                    ? ($axisIndex / ($weeklyProductivity->count() - 1)) * 100
                                    : 0;
                                $translate = $loop->first ? '0' : ($loop->last ? '-100%' : '-50%');
                            @endphp
                            <div class="task-productivity-axis-label" style="position: absolute; left: {{ $leftPercent }}%; transform: translateX({{ $translate }}); display: inline-flex; flex-direction: column; align-items: center; line-height: 1.05; max-width: 64px; text-align: center;">
                                <span class="task-productivity-axis-month" style="font-size: 8px; font-weight: 900; color: {{ $axisWeek['is_current_week'] ? 'var(--primary)' : 'var(--text-muted)' }}; text-transform: uppercase; letter-spacing: 0.06em;">{{ $axisWeek['short_label'] }}</span>
                                <span class="task-productivity-axis-day" style="font-size: 10px; font-weight: 800; color: var(--text-main); opacity: 0.72;">{{ \Carbon\Carbon::parse($axisWeek['week_start'])->format('d') }}</span>
                            </div>
                        @endforeach
                    </div>
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
                    <h3 style="font-size: 18px; font-weight: 900;">{{ __('Kids Points Overview') }}</h3>
                    <p style="font-size: 11px; color: var(--text-muted);">{{ __('Click a child to quick-assign points') }}</p>
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
                                    <div style="font-size: 1.15rem; font-weight: 900; color: var(--primary); margin: 2px 0;">{{ $child->accumulated_score }} <span style="font-size: 9px; opacity: 0.7;">{{ __('PTS AVAILABLE') }}</span></div>
                                    
                                    <!-- Progress Bar -->
                                    <div style="width: 80px; height: 5px; background: var(--bg-input); border-radius: 10px; margin: 6px auto 8px auto; overflow: hidden; border: 1px solid var(--border-color); position: relative;">
                                        <div style="width: {{ $child->monthly_goal_progress }}%; height: 100%; background: @if($child->monthly_goal_progress >= 100) var(--success) @else var(--primary) @endif; border-radius: 10px; transition: width 0.8s ease-out;"></div>
                                    </div>

                                    <div style="font-size: 10px; font-weight: 800; color: @if($child->monthly_goal_progress >= 100) var(--success) @else var(--text-muted) @endif; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                                        {{ __('Target') }}: {{ $child->monthly_points }} / {{ $child->monthly_points_goal }}
                                    </div>
                                </div>
                                
                                <div class="hover-assign-overlay" style="position: absolute; inset: 0; background: var(--success); color: white; display: flex; align-items: center; justify-content: center; opacity: 0; transition: all 0.2s; transform: translateY(100%);">
                                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 900; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                                        {{ __('Quick Assign') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 160px; color: var(--text-muted); text-align: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.3; margin-bottom: 12px;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <div style="font-size: 13px; font-weight: 800;">{{ __('No children registered') }}</div>
                        <p style="font-size: 11px; margin-top: 4px;">{{ __('Tag users as children in the') }} <a href="{{ route('admin.users') }}" style="color: var(--primary); font-weight: 800; text-decoration: underline;">{{ __('Admin panel') }}</a></p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Quick Assign Modal -->
    @if($showQuickAssignModal)
    <template x-teleport="body" wire:key="modal-dashboard-quick-assign">
        <div class="modal-overlay" @click.self="$wire.set('showQuickAssignModal', false)">
            <div class="modal-content" style="max-width: 500px;">
                <div style="padding: 28px 32px 24px 32px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-input);">
                    <div>
                        <h3 style="font-size: 1.5rem; font-weight: 900; color: var(--success); font-family: var(--font-heading);">{{ __('Quick Assign') }}</h3>
                        <p style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px;">{{ __('Assign to') }} {{ $quickAssignUserName }}</p>
                    </div>
                    <button @click="$wire.set('showQuickAssignModal', false)" style="background: var(--bg-card); border: 1px solid var(--border-color); width: 36px; height: 36px; border-radius: 50%; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 20px;">×</button>
                </div>
                
                <div style="padding: 24px 32px 32px 32px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding: 12px 20px; background: var(--bg-input); border-radius: 16px; border: 1px solid var(--border-color);">
                        <div style="font-size: 13px; font-weight: 800; color: var(--text-main);">{{ __('Mark as completed immediately') }}</div>
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
                                        <div style="font-size: 12px; font-weight: 900; color: var(--primary); margin-top: 2px;">+{{ $template->score }} {{ __('Points') }}</div>
                                    </div>
                                    <div style="background: var(--bg-input); color: var(--primary); width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                    </div>
                                </div>
                            </button>
                        @empty
                            <div style="text-align: center; padding: 40px 20px; color: var(--text-muted); border: 2px dashed var(--border-color); border-radius: 20px;">
                                <p style="font-size: 13px; font-weight: 800;">{{ __('No templates found') }}</p>
                                <a href="{{ route('kids') }}" class="btn" style="color: var(--primary); font-size: 12px; font-weight: 800; margin-top: 8px;">{{ __('Go to Kids System') }}</a>
                            </div>
                        @endforelse
                    </div>

                    <div style="margin-top: 24px;">
                        <button @click="$wire.set('showQuickAssignModal', false)" class="btn" style="width: 100%; background: var(--bg-input); color: var(--text-muted); border: 1px solid var(--border-color); padding: 14px; border-radius: 16px; font-weight: 800;">{{ __('Cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </template>

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

        .task-productivity-frame {
            min-width: 0;
        }

        .task-productivity-header {
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.04) 0%, rgba(37, 99, 235, 0) 100%) !important;
        }

        .task-productivity-body {
            background: linear-gradient(180deg, var(--bg-card) 0%, var(--bg-input) 100%) !important;
        }

        .task-productivity-plot {
            overflow: hidden;
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.04) 0%, rgba(37, 99, 235, 0) 100%) !important;
            border-color: rgba(148, 163, 184, 0.18) !important;
        }

        .task-productivity-axis-label {
            max-width: 100%;
        }

        .task-productivity-axis-month,
        .task-productivity-axis-day {
            display: block;
            max-width: 100%;
        }

        html.dark .task-productivity-header {
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.12) 0%, rgba(37, 99, 235, 0.02) 100%) !important;
        }

        html.dark .task-productivity-body {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.96) 0%, rgba(30, 41, 59, 0.92) 100%) !important;
        }

        html.dark .task-productivity-pill {
            background: rgba(15, 23, 42, 0.42) !important;
            border-color: rgba(148, 163, 184, 0.14) !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03), var(--shadow-sm) !important;
        }

        html.dark .task-productivity-tag {
            background: rgba(22, 163, 74, 0.14) !important;
        }

        html.dark .task-productivity-plot {
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.18) 0%, rgba(15, 23, 42, 0.18) 100%) !important;
            border-color: rgba(148, 163, 184, 0.12) !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.02);
        }

        @media (max-width: 640px) {
            .task-productivity-header {
                padding: 20px;
                align-items: stretch !important;
            }

            .task-productivity-summary {
                width: 100%;
                margin-left: 0 !important;
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .task-productivity-pill {
                min-width: 0 !important;
                text-align: center !important;
                padding: 12px 10px !important;
            }

            .task-productivity-tag {
                grid-column: 1 / -1;
                justify-self: center;
            }

            .task-productivity-body {
                padding: 20px 14px 16px !important;
                gap: 14px !important;
            }

            .task-productivity-chart-shell {
                grid-template-columns: 24px 1fr !important;
                gap: 8px !important;
            }

            .task-productivity-scale {
                font-size: 9px !important;
                padding-top: 8px !important;
            }

            .task-productivity-frame {
                overflow: hidden;
            }

            .task-productivity-plot {
                min-height: 196px !important;
                padding: 12px 8px 10px !important;
                min-width: 0;
            }

            .task-productivity-grid {
                inset: 12px 8px 12px !important;
            }

            .task-productivity-bars {
                gap: 4px !important;
                min-width: 0 !important;
                width: 100% !important;
            }

            .task-productivity-week {
                gap: 6px !important;
            }

            .task-productivity-value {
                font-size: 9px !important;
                min-height: 12px !important;
            }

            .task-productivity-bar-track {
                min-height: 118px;
            }

            .task-productivity-axis {
                height: 18px !important;
                margin-top: 8px !important;
            }

            .task-productivity-axis-month {
                display: none !important;
            }

            .task-productivity-axis-day {
                font-size: 8px !important;
                line-height: 1 !important;
            }

        }
    </style>
    @endif
</div>

