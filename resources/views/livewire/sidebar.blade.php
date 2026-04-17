<aside {{ $attributes->merge(['class' => 'sidebar']) }} x-data="{ mobileTray: null }" x-init="window.addEventListener('livewire:navigated', () => { mobileTray = null; })" @keydown.escape.window="mobileTray = null">
    <div class="sidebar-desktop-shell">
    @if(session()->has('impersonator_id'))
        <div style="background: var(--warning); color: var(--slate-950); padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                {{ __('Impersonating') }} {{ auth()->user()->name }}
            </div>
            <button wire:click="stopImpersonating" style="background: white; color: var(--slate-950); border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer; font-weight: 900; font-size: 10px; text-transform: uppercase; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">{{ __('Stop') }}</button>
        </div>
    @endif
        <!-- Workspace Header -->
        <a href="/" wire:navigate class="sidebar-header" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: var(--space-3); padding: var(--space-8) var(--space-6);">
            <div class="sidebar-icon-box">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="M17 14h-6"/><path d="M13 18H7"/><path d="M7 14h.01"/><path d="M17 18h.01"/></svg>
            </div>
            <div>
                <span class="badge badge-soft" style="color: var(--primary); background: var(--primary-soft);">{{ __('Workspace') }}</span>
                <h1 style="font-size: 1.25rem; font-weight: 900; line-height: 1; margin: 0;">{{ __('Homeplanner') }}</h1>
                <p style="font-size: 11px; color: var(--text-muted); margin: 4px 0 0 0;">{{ __('Plan with clarity') }}</p>
            </div>
        </a>

        <!-- Navigation -->
        <nav class="sidebar-nav">
            <div>
                <h2 class="nav-group-title">{{ __('Navigation') }}</h2>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <a href="{{ route('home') }}" wire:navigate class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 700;">{{ __('Home') }}</div>
                            <div style="font-size: 10px; opacity: 0.7;">{{ __('Overview and charts') }}</div>
                        </div>
                    </a>

                    @if($economyEnabled)
                    <div x-data="{ open: {{ request()->routeIs('economy.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="nav-link {{ request()->routeIs('economy.*') ? 'active' : '' }}" style="width: 100%; border: none; background: transparent; cursor: pointer; text-align: left; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div class="nav-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="M17 14h-6"/><path d="M13 18H7"/><path d="M7 14h.01"/><path d="M17 18h.01"/></svg>
                                </div>
                                <div>
                                    <div style="font-size: 14px; font-weight: 700;">{{ __('Economy') }}</div>
                                    <div style="font-size: 10px; opacity: 0.7;">{{ __('Shopping and budgets') }}</div>
                                </div>
                            </div>
                            <svg :style="open ? 'transform: rotate(180deg)' : ''" style="width: 14px; height: 14px; transition: transform 0.3s;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        
                        <div x-show="open" class="nav-sub-container">
                            <a href="{{ route('economy.index') }}" wire:navigate class="nav-sub-link {{ request()->routeIs('economy.index') && !request()->is('economy/savings') && !request()->is('economy/history') && !request()->is('economy/savings-history') ? 'active' : '' }}">{{ __('Current month') }}</a>
                            <a href="{{ route('economy.savings') }}" wire:navigate class="nav-sub-link {{ request()->routeIs('economy.savings') ? 'active' : '' }}">{{ __('Savings') }}</a>
                            <a href="{{ route('economy.history') }}" wire:navigate class="nav-sub-link {{ request()->routeIs('economy.history') ? 'active' : '' }}">{{ __('Monthly history') }}</a>
                            <a href="{{ route('economy.savings-history') }}" wire:navigate class="nav-sub-link {{ request()->routeIs('economy.savings-history') ? 'active' : '' }}">{{ __('Savings history') }}</a>
                        </div>
                    </div>
                    @endif

                    @if($shoppingEnabled)
                    <a href="{{ route('shopping.index') }}" wire:navigate class="nav-link {{ request()->routeIs('shopping.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 700;">{{ __('Shopping') }}</div>
                            <div style="font-size: 10px; opacity: 0.7;">{{ __('Shared grocery lists') }}</div>
                        </div>
                    </a>
                    @endif

                    @if($todoEnabled)
                    <a href="{{ route('todo.index') }}" wire:navigate class="nav-link {{ request()->routeIs('todo.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 700;">{{ __('Todo') }}</div>
                            <div style="font-size: 10px; opacity: 0.7;">{{ __('Shared task lists') }}</div>
                        </div>
                    </a>
                    @endif

                    @if($kidsEnabled)
                    <a href="{{ route('kids.index') }}" wire:navigate class="nav-link {{ request()->routeIs('kids.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="font-size: 14px; font-weight: 700;">{{ __('Kids System') }}</div>
                            </div>
                            <div style="font-size: 10px; opacity: 0.7;">{{ __('Points and rewards') }}</div>
                        </div>
                    </a>
                    @endif

                    @if(!auth()->user()->is_child)
                    <div x-data="{ open: {{ request()->is('admin/*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="nav-link {{ request()->is('admin/*') ? 'active' : '' }}" style="width: 100%; border: none; background: transparent; cursor: pointer; text-align: left; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div class="nav-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                                </div>
                                <div>
                                    <div style="font-size: 14px; font-weight: 700;">{{ __('Admin') }}</div>
                                    <div style="font-size: 10px; opacity: 0.7;">{{ __('Users and settings') }}</div>
                                </div>
                            </div>
                            <svg :style="open ? 'transform: rotate(180deg)' : ''" style="width: 14px; height: 14px; transition: transform 0.3s;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        
                        <div x-show="open" class="nav-sub-container">
                            <a href="{{ route('admin.users') }}" wire:navigate class="nav-sub-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">{{ __('User list') }}</a>
                            <a href="/admin/settings" wire:navigate class="nav-sub-link {{ request()->is('admin/settings') ? 'active' : '' }}">{{ __('Application settings') }}</a>
                            <a href="/admin/settings#auth" wire:navigate class="nav-sub-link">{{ __('Authentication') }}</a>
                            <a href="{{ route('admin.logs') }}" wire:navigate class="nav-sub-link {{ request()->routeIs('admin.logs') ? 'active' : '' }}">{{ __('Audit log') }}</a>
                            <a href="{{ route('admin.versions') }}" wire:navigate class="nav-sub-link {{ request()->routeIs('admin.versions') ? 'active' : '' }}">{{ __('App versions') }}</a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </nav>

        <!-- Footer / User Profile -->
        <div class="sidebar-footer">
            <!-- Appearance -->
            <div x-data="{ 
                    localDarkMode: localStorage.getItem('darkMode') === 'true'
                }"
                x-init="
                    window.addEventListener('apply-theme', e => {
                        localDarkMode = e.detail.darkMode;
                    });
                "
                wire:ignore
                style="padding: 16px; border-radius: 20px; background: var(--bg-input); border: 1px solid var(--border-color); margin-bottom: 12px;">
                <h3 style="font-size: 13px; font-weight: 800; margin-bottom: 2px;">{{ __('Appearance') }}</h3>
                <p style="font-size: 10px; color: var(--text-muted); margin-bottom: 12px;">{{ __('Syncs with browser theme.') }}</p>
                
                <button @click="localDarkMode = !localDarkMode; window.dispatchEvent(new CustomEvent('apply-theme', { detail: { darkMode: localDarkMode } }))" 
                        wire:click="toggleDarkMode" 
                        style="width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 12px; border-radius: 12px; background: var(--bg-sidebar); border: 1px solid var(--border-color); cursor: pointer; transition: transform 0.2s;">
                    <span style="font-size: 13px; font-weight: 700; color: var(--text-main);" x-text="localDarkMode ? '{{ __('Dark mode on') }}' : '{{ __('Light mode on') }}'">
                        {{ $darkMode ? __('Dark mode on') : __('Light mode on') }}
                    </span>
                    <div style="width: 40px; height: 20px; border-radius: 20px; position: relative; transition: background 0.3s;"
                         :style="{ background: localDarkMode ? 'var(--primary)' : 'var(--border-strong)' }">
                        <div style="position: absolute; top: 2px; width: 16px; height: 16px; border-radius: 50%; background: white; transition: all 0.3s;"
                             :style="{ left: localDarkMode ? '22px' : '2px' }"></div>
                    </div>
                </button>
            </div>

            <!-- Language Selector -->
            <div style="padding: 16px; border-radius: 20px; background: var(--bg-input); border: 1px solid var(--border-color); margin-bottom: 16px;">
                <h3 style="font-size: 13px; font-weight: 800; margin-bottom: 12px;">{{ __('Language') }}</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <button wire:click="setLocale('en')" 
                            style="padding: 8px; border-radius: 10px; border: 1px solid {{ $currentLocale === 'en' ? 'var(--primary)' : 'var(--border-color)' }}; background: {{ $currentLocale === 'en' ? 'var(--primary-soft)' : 'var(--bg-sidebar)' }}; color: {{ $currentLocale === 'en' ? 'var(--primary)' : 'var(--text-main)' }}; cursor: pointer; font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s;">
                        🇺🇸 {{ __('English') }}
                    </button>
                    <button wire:click="setLocale('sv')" 
                            style="padding: 8px; border-radius: 10px; border: 1px solid {{ $currentLocale === 'sv' ? 'var(--primary)' : 'var(--border-color)' }}; background: {{ $currentLocale === 'sv' ? 'var(--primary-soft)' : 'var(--bg-sidebar)' }}; color: {{ $currentLocale === 'sv' ? 'var(--primary)' : 'var(--text-main)' }}; cursor: pointer; font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s;">
                        🇸🇪 {{ __('Swedish') }}
                    </button>
                </div>
            </div>

            <div style="margin-bottom: 24px; padding: 0 16px; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">{{ __('Application') }}</span>
                <span class="badge badge-soft" style="font-size: 10px; font-weight: 800; background: var(--bg-input); color: var(--text-muted); border: 1px solid var(--border-color);">{{ $appVersion }}</span>
            </div>
            @auth
            <div style="padding: 16px; border-radius: 24px; background: var(--bg-sidebar); border: 1px solid var(--border-color); box-shadow: var(--shadow); display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div style="flex: 1; min-width: 0;">
                    <p style="font-size: 10px; font-weight: 900; color: var(--primary); text-transform: uppercase; margin-bottom: 2px;">{{ __('Signed in as') }}</p>
                    <h4 style="font-size: 14px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ auth()->user()->name }}</h4>
                    <p style="font-size: 10px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ auth()->user()->email }}</p>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; flex-direction: column; gap: 8px;">
                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="submit" class="nav-link" style="width: 100%; text-align: left; background: transparent; border: none; color: var(--danger); font-weight: 800; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 12px; padding: 12px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        {{ __('Sign out') }}
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </div>

    <div class="mobile-nav-backdrop" x-cloak x-show="mobileTray" x-transition.opacity @click="mobileTray = null"></div>

    <div class="mobile-nav-sheet-wrap" x-cloak x-show="mobileTray" x-transition.opacity>
        <div class="mobile-nav-sheet" @click.outside="mobileTray = null" x-transition:enter="mobile-sheet-enter" x-transition:enter-start="mobile-sheet-enter-start" x-transition:enter-end="mobile-sheet-enter-end" x-transition:leave="mobile-sheet-leave" x-transition:leave-start="mobile-sheet-leave-start" x-transition:leave-end="mobile-sheet-leave-end">
            <div x-show="mobileTray === 'economy'" x-cloak>
                <div class="mobile-nav-sheet-header">
                    <div>
                        <p class="mobile-nav-sheet-eyebrow">{{ __('Navigation') }}</p>
                        <h3>{{ __('Economy') }}</h3>
                    </div>
                    <button type="button" class="mobile-nav-sheet-close" @click="mobileTray = null" aria-label="{{ __('Cancel') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
                    </button>
                </div>

                <div class="mobile-nav-sheet-links">
                    <a href="{{ route('economy.index') }}" wire:navigate x-on:click="mobileTray = null" class="mobile-nav-sheet-link {{ request()->routeIs('economy.index') && !request()->is('economy/savings') && !request()->is('economy/history') && !request()->is('economy/savings-history') ? 'active' : '' }}">
                        <span>{{ __('Current month') }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                    <a href="{{ route('economy.savings') }}" wire:navigate x-on:click="mobileTray = null" class="mobile-nav-sheet-link {{ request()->routeIs('economy.savings') ? 'active' : '' }}">
                        <span>{{ __('Savings') }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                    <a href="{{ route('economy.history') }}" wire:navigate x-on:click="mobileTray = null" class="mobile-nav-sheet-link {{ request()->routeIs('economy.history') ? 'active' : '' }}">
                        <span>{{ __('Monthly history') }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                    <a href="{{ route('economy.savings-history') }}" wire:navigate x-on:click="mobileTray = null" class="mobile-nav-sheet-link {{ request()->routeIs('economy.savings-history') ? 'active' : '' }}">
                        <span>{{ __('Savings history') }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </div>

            <div x-show="mobileTray === 'more'" x-cloak>
                <div class="mobile-nav-sheet-header">
                    <div>
                        <p class="mobile-nav-sheet-eyebrow">{{ __('Application') }}</p>
                        <h3>{{ __('More') }}</h3>
                    </div>
                    <button type="button" class="mobile-nav-sheet-close" @click="mobileTray = null" aria-label="{{ __('Cancel') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
                    </button>
                </div>

                @auth
                <div class="mobile-account-card">
                    <div class="mobile-account-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="mobile-account-meta">
                        <p class="mobile-account-eyebrow">{{ __('Signed in as') }}</p>
                        <h4>{{ auth()->user()->name }}</h4>
                        <p>{{ auth()->user()->email }}</p>
                    </div>
                    <span class="mobile-account-version">{{ $appVersion }}</span>
                </div>
                @endauth

                @if(session()->has('impersonator_id'))
                <div class="mobile-sheet-block" style="background: var(--warning-soft); border-color: rgba(217, 119, 6, 0.2);">
                    <div class="mobile-sheet-block-head">
                        <div>
                            <p class="mobile-nav-sheet-eyebrow">{{ __('Admin') }}</p>
                            <h4 style="font-size: 0.95rem; font-weight: 800;">{{ __('Impersonating') }} {{ auth()->user()->name }}</h4>
                        </div>
                        <button wire:click="stopImpersonating" class="mobile-inline-action">{{ __('Stop') }}</button>
                    </div>
                </div>
                @endif

                @if(!auth()->user()->is_child)
                <div class="mobile-sheet-block">
                    <div class="mobile-sheet-block-head">
                        <div>
                            <p class="mobile-nav-sheet-eyebrow">{{ __('Navigation') }}</p>
                            <h4>{{ __('Admin') }}</h4>
                        </div>
                    </div>
                    <div class="mobile-nav-sheet-links compact">
                        <a href="{{ route('admin.users') }}" wire:navigate x-on:click="mobileTray = null" class="mobile-nav-sheet-link {{ request()->routeIs('admin.users') ? 'active' : '' }}"><span>{{ __('User list') }}</span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></a>
                        <a href="/admin/settings" wire:navigate x-on:click="mobileTray = null" class="mobile-nav-sheet-link {{ request()->is('admin/settings') ? 'active' : '' }}"><span>{{ __('Application settings') }}</span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></a>
                        <a href="/admin/settings#auth" wire:navigate x-on:click="mobileTray = null" class="mobile-nav-sheet-link"><span>{{ __('Authentication') }}</span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></a>
                        <a href="{{ route('admin.logs') }}" wire:navigate x-on:click="mobileTray = null" class="mobile-nav-sheet-link {{ request()->routeIs('admin.logs') ? 'active' : '' }}"><span>{{ __('Audit log') }}</span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></a>
                        <a href="{{ route('admin.versions') }}" wire:navigate x-on:click="mobileTray = null" class="mobile-nav-sheet-link {{ request()->routeIs('admin.versions') ? 'active' : '' }}"><span>{{ __('App versions') }}</span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></a>
                    </div>
                </div>
                @endif

                <div class="mobile-sheet-grid">
                    <div x-data="{ localDarkMode: localStorage.getItem('darkMode') === 'true' }"
                        x-init="window.addEventListener('apply-theme', e => { localDarkMode = e.detail.darkMode; });"
                        wire:ignore
                        class="mobile-sheet-block">
                        <div class="mobile-sheet-block-head">
                            <div>
                                <p class="mobile-nav-sheet-eyebrow">{{ __('Appearance') }}</p>
                                <h4>{{ __('Syncs with browser theme.') }}</h4>
                            </div>
                        </div>
                        <button @click="localDarkMode = !localDarkMode; window.dispatchEvent(new CustomEvent('apply-theme', { detail: { darkMode: localDarkMode } }))"
                            wire:click="toggleDarkMode"
                            class="mobile-settings-toggle">
                            <span x-text="localDarkMode ? '{{ __('Dark mode on') }}' : '{{ __('Light mode on') }}'">
                                {{ $darkMode ? __('Dark mode on') : __('Light mode on') }}
                            </span>
                            <div class="mobile-settings-switch" :class="{ 'active': localDarkMode }">
                                <div class="mobile-settings-switch-thumb" :class="{ 'active': localDarkMode }"></div>
                            </div>
                        </button>
                    </div>

                    <div class="mobile-sheet-block">
                        <div class="mobile-sheet-block-head">
                            <div>
                                <p class="mobile-nav-sheet-eyebrow">{{ __('Language') }}</p>
                                <h4>{{ __('Application') }}</h4>
                            </div>
                        </div>
                        <div class="mobile-language-grid">
                            <button wire:click="setLocale('en')" class="mobile-language-button {{ $currentLocale === 'en' ? 'active' : '' }}">🇺🇸 {{ __('English') }}</button>
                            <button wire:click="setLocale('sv')" class="mobile-language-button {{ $currentLocale === 'sv' ? 'active' : '' }}">🇸🇪 {{ __('Swedish') }}</button>
                        </div>
                    </div>
                </div>

                @auth
                <form method="POST" action="{{ route('logout') }}" id="mobile-logout-form">
                    @csrf
                    <button type="submit" class="mobile-signout-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        {{ __('Sign out') }}
                    </button>
                </form>
                @endauth
            </div>
        </div>
    </div>

    <nav class="mobile-tabbar" aria-label="{{ __('Navigation') }}">
        <a href="{{ route('home') }}" wire:navigate x-on:click="mobileTray = null" class="mobile-tabbar-item {{ request()->routeIs('home') ? 'active' : '' }}">
            <span class="mobile-tabbar-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </span>
            <span class="mobile-tabbar-label">{{ __('Home') }}</span>
        </a>

        @if($economyEnabled)
        <button type="button" @click="mobileTray = mobileTray === 'economy' ? null : 'economy'" class="mobile-tabbar-item {{ request()->routeIs('economy.*') ? 'active' : '' }}" :class="{ 'open': mobileTray === 'economy' }">
            <span class="mobile-tabbar-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="M17 14h-6"/><path d="M13 18H7"/><path d="M7 14h.01"/><path d="M17 18h.01"/></svg>
            </span>
            <span class="mobile-tabbar-label">{{ __('Economy') }}</span>
        </button>
        @endif

        @if($shoppingEnabled)
        <a href="{{ route('shopping.index') }}" wire:navigate x-on:click="mobileTray = null" class="mobile-tabbar-item {{ request()->routeIs('shopping.*') ? 'active' : '' }}">
            <span class="mobile-tabbar-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
            </span>
            <span class="mobile-tabbar-label">{{ __('Shopping') }}</span>
        </a>
        @endif

        @if($todoEnabled)
        <a href="{{ route('todo.index') }}" wire:navigate x-on:click="mobileTray = null" class="mobile-tabbar-item {{ request()->routeIs('todo.*') ? 'active' : '' }}">
            <span class="mobile-tabbar-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </span>
            <span class="mobile-tabbar-label">{{ __('Todo') }}</span>
        </a>
        @endif

        @if($kidsEnabled)
        <a href="{{ route('kids.index') }}" wire:navigate x-on:click="mobileTray = null" class="mobile-tabbar-item {{ request()->routeIs('kids.*') ? 'active' : '' }}">
            <span class="mobile-tabbar-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <span class="mobile-tabbar-label">{{ __('Kids') }}</span>
        </a>
        @endif

        <button type="button" @click="mobileTray = mobileTray === 'more' ? null : 'more'" class="mobile-tabbar-item {{ request()->is('admin/*') ? 'active' : '' }}" :class="{ 'open': mobileTray === 'more' }">
            <span class="mobile-tabbar-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/><circle cx="5" cy="12" r="1.5"/></svg>
            </span>
            <span class="mobile-tabbar-label">{{ __('More') }}</span>
        </button>
    </nav>
</aside>
