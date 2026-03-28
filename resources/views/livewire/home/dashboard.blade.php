<div class="animate-in">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-8); padding-bottom: var(--space-6); border-bottom: 1px solid var(--border-color); flex-wrap: wrap; gap: 16px;">
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
        <div class="summary-card">
            <div class="summary-label">Total Savings</div>
            <div class="summary-value" style="color: var(--primary);">{{ number_format($totalSavings, 0, ',', ' ') }} <span style="font-size: 0.5em; opacity: 1;">KR</span></div>
            <div style="font-size: 11px; margin-top: var(--space-3); color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary);"></div>
                Accumulated Capital
            </div>
        </div>
        @endif
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
                    <p style="font-size: 11px; color: var(--text-muted);">Current accumulated household scores</p>
                </div>
                <div style="text-align: right;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
            </div>
            <div style="padding: var(--space-6) var(--space-8); background: var(--bg-input); min-height: 200px;">
                @if($children->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @foreach($children as $child)
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px; background: var(--bg-card); border-radius: 20px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                                <div style="display: flex; align-items: center; gap: 14px;">
                                    <div style="width: 38px; height: 38px; border-radius: 12px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px; box-shadow: 0 4px 8px rgba(37, 99, 235, 0.2);">
                                        {{ substr($child->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 800; font-size: 14px; color: var(--text-main);">{{ $child->name }}</div>
                                        <div style="display: flex; gap: 8px; margin-top: 2px;">
                                            <div style="font-size: 10px; font-weight: 800; color: var(--success); display: flex; align-items: center; gap: 4px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                                                {{ $child->monthly_points }} this month
                                            </div>
                                            <div style="font-size: 10px; font-weight: 700; color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                                {{ $child->total_finished_tasks }} chores
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: 900; color: var(--primary); font-size: 16px;">{{ $child->accumulated_score }}</div>
                                    <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Total PTS</div>
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
    </div>
</div>
