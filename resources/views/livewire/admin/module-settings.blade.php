<div class="card">
    <div class="card-header">
        <h3 style="font-weight: 900;">Module Settings</h3>
        <p style="font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Enable or disable system modules</p>
    </div>
    <div class="card-body">
        @if (session()->has('message'))
            <div style="background: var(--success-soft); color: var(--success); padding: 12px; border-radius: 12px; margin-bottom: 20px; font-weight: 700; font-size: 13px;">
                {{ session('message') }}
            </div>
        @endif

        <form wire:submit.prevent="save" style="display: flex; flex-direction: column; gap: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                <!-- Economy Module -->
                <div style="padding: 16px; border-radius: 20px; background: var(--bg-input); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 12px; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="M17 14h-6"/><path d="M13 18H7"/><path d="M7 14h.01"/><path d="M17 18h.01"/></svg>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 800;">Economy</div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Budgets & Ledger</div>
                        </div>
                    </div>
                    <button type="button" @click="$wire.set('economyEnabled', !$wire.economyEnabled)" 
                            style="width: 48px; height: 24px; border-radius: 24px; position: relative; border: none; cursor: pointer; transition: background 0.3s; padding: 0;"
                            :style="{ background: $wire.economyEnabled ? 'var(--primary)' : 'var(--border-strong)' }">
                        <div style="position: absolute; top: 3px; width: 18px; height: 18px; border-radius: 50%; background: white; transition: all 0.3s; box-shadow: var(--shadow-sm);"
                             :style="{ left: $wire.economyEnabled ? '27px' : '3px' }"></div>
                    </button>
                </div>

                <!-- Shopping Module -->
                <div style="padding: 16px; border-radius: 20px; background: var(--bg-input); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 12px; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 800;">Shopping</div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Grocery Lists</div>
                        </div>
                    </div>
                    <button type="button" @click="$wire.set('shoppingEnabled', !$wire.shoppingEnabled)" 
                            style="width: 48px; height: 24px; border-radius: 24px; position: relative; border: none; cursor: pointer; transition: background 0.3s; padding: 0;"
                            :style="{ background: $wire.shoppingEnabled ? 'var(--primary)' : 'var(--border-strong)' }">
                        <div style="position: absolute; top: 3px; width: 18px; height: 18px; border-radius: 50%; background: white; transition: all 0.3s; box-shadow: var(--shadow-sm);"
                             :style="{ left: $wire.shoppingEnabled ? '27px' : '3px' }"></div>
                    </button>
                </div>

                <!-- Todo Module -->
                <div style="padding: 16px; border-radius: 20px; background: var(--bg-input); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 12px; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 800;">Todo</div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Task Management</div>
                        </div>
                    </div>
                    <button type="button" @click="$wire.set('todoEnabled', !$wire.todoEnabled)" 
                            style="width: 48px; height: 24px; border-radius: 24px; position: relative; border: none; cursor: pointer; transition: background 0.3s; padding: 0;"
                            :style="{ background: $wire.todoEnabled ? 'var(--primary)' : 'var(--border-strong)' }">
                        <div style="position: absolute; top: 3px; width: 18px; height: 18px; border-radius: 50%; background: white; transition: all 0.3s; box-shadow: var(--shadow-sm);"
                             :style="{ left: $wire.todoEnabled ? '27px' : '3px' }"></div>
                    </button>
                </div>

                <!-- Kids System Module -->
                <div style="padding: 16px; border-radius: 20px; background: var(--bg-input); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 12px; background: var(--primary-soft); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 800;">Kids System</div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Points & Rewards</div>
                        </div>
                    </div>
                    <button type="button" @click="$wire.set('kidsEnabled', !$wire.kidsEnabled)" 
                            style="width: 48px; height: 24px; border-radius: 24px; position: relative; border: none; cursor: pointer; transition: background 0.3s; padding: 0;"
                            :style="{ background: $wire.kidsEnabled ? 'var(--primary)' : 'var(--border-strong)' }">
                        <div style="position: absolute; top: 3px; width: 18px; height: 18px; border-radius: 50%; background: white; transition: all 0.3s; box-shadow: var(--shadow-sm);"
                             :style="{ left: $wire.kidsEnabled ? '27px' : '3px' }"></div>
                    </button>
                </div>
            </div>

            <div style="margin-top: 10px; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn-primary" style="padding: 10px 24px; border-radius: 12px; background: var(--primary); color: white; border: none; font-weight: 800; cursor: pointer;">
                    Save Module Settings
                </button>
            </div>
        </form>
    </div>
</div>
