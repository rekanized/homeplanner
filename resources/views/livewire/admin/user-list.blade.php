<div style="max-width: 1280px; margin: 0 auto; padding: 40px 24px;">
    <div style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 900; color: var(--text-main); font-family: var(--font-heading); margin-bottom: 8px;">Registered Users</h1>
            <p style="color: var(--text-muted); font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em;">Household member registration log</p>
        </div>
        <div style="display: flex; align-items: center; gap: 16px;">
            <button wire:click="openCreateModal" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="16" x2="22" y1="11" y2="11"/></svg>
                Create Manual User
            </button>
            <div style="background: var(--bg-card); padding: 12px 20px; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                <span style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Total Members:</span>
                <span style="font-size: 18px; font-weight: 900; color: var(--primary); margin-left: 8px;">{{ $users->count() }}</span>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div style="background: var(--success-soft); color: var(--success); padding: 16px; border-radius: 16px; margin-bottom: 24px; font-weight: 700; font-size: 14px; border: 1px solid var(--success);">
            {{ session('message') }}
        </div>
    @endif

    <div class="card" style="overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-input); border-bottom: 2px solid var(--border-color);">
                    <th style="padding: 16px 24px; text-align: left; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Member</th>
                    <th style="padding: 16px 24px; text-align: left; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Method</th>
                    <th style="padding: 16px 24px; text-align: left; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Joined Date</th>
                    <th style="padding: 16px 24px; text-align: right; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Status</th>
                </tr>
            </thead>
            <tbody style="background: var(--bg-card);">
                @foreach($users as $user)
                    <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.2s ease;">
                        <td style="padding: 20px 24px;">
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div style="width: 44px; height: 44px; border-radius: 12px; overflow: hidden; background: var(--bg-input); border: 2px solid var(--border-color);">
                                    @if($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 900; color: var(--primary);">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div style="font-size: 15px; font-weight: 800; color: var(--text-main);">{{ $user->name }}</div>
                                    <div style="font-size: 13px; font-weight: 600; color: var(--text-muted);">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 20px 24px;">
                            @if($user->google_id)
                                <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 8px; background: var(--primary-soft); color: var(--primary); font-size: 11px; font-weight: 800; text-transform: uppercase;">
                                    Google Auth
                                </div>
                            @else
                                <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 8px; background: var(--bg-input); color: var(--text-muted); font-size: 11px; font-weight: 800; text-transform: uppercase; border: 1px solid var(--border-color);">
                                    Manual
                                </div>
                            @endif
                        </td>
                        <td style="padding: 20px 24px;">
                            <div style="font-size: 14px; font-weight: 700; color: var(--text-main);">{{ $user->created_at->format('M j, Y') }}</div>
                            <div style="font-size: 12px; font-weight: 500; color: var(--text-muted);">{{ $user->created_at->format('H:i') }}</div>
                        </td>
                        <td style="padding: 20px 24px; text-align: right;">
                            <span style="display: inline-block; padding: 4px 12px; border-radius: 100px; background: var(--success-soft); color: var(--success); font-size: 11px; font-weight: 800; text-transform: uppercase;">Active</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Create Modal -->
    @if($showCreateModal)
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(8px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div @click.away="$wire.set('showCreateModal', false)" style="background: var(--bg-card); width: 100%; max-width: 450px; border-radius: 24px; box-shadow: var(--shadow-xl); border: 1px solid var(--border-color); overflow: hidden; animation: slideUp 0.3s ease-out;">
                <div style="padding: 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 1.25rem; font-weight: 900; color: var(--text-main);">Create Manual User</h3>
                    <button @click="$wire.set('showCreateModal', false)" style="background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 24px;">×</button>
                </div>
                <form wire:submit.prevent="createUser" style="padding: 24px; display: flex; flex-direction: column; gap: 20px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">Full Name</label>
                        <input type="text" wire:model="name" style="background: var(--bg-input); color: var(--text-main); border: 1px solid var(--border-color); padding: 12px 16px; border-radius: 12px; width: 100%; font-size: 14px; font-weight: 600;" placeholder="E.g. Magnus Andersson">
                        @error('name') <p style="font-size: 11px; color: var(--danger); margin-top: 4px; font-weight: 700;">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">Email Address</label>
                        <input type="email" wire:model="email" style="background: var(--bg-input); color: var(--text-main); border: 1px solid var(--border-color); padding: 12px 16px; border-radius: 12px; width: 100%; font-size: 14px; font-weight: 600;" placeholder="magnus@example.com">
                        @error('email') <p style="font-size: 11px; color: var(--danger); margin-top: 4px; font-weight: 700;">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">Password</label>
                        <input type="password" wire:model="password" style="background: var(--bg-input); color: var(--text-main); border: 1px solid var(--border-color); padding: 12px 16px; border-radius: 12px; width: 100%; font-size: 14px; font-weight: 600;" placeholder="••••••••">
                        @error('password') <p style="font-size: 11px; color: var(--danger); margin-top: 4px; font-weight: 700;">{{ $message }}</p> @enderror
                    </div>
                    <div style="display: flex; gap: 12px; margin-top: 8px;">
                        <button type="button" @click="$wire.set('showCreateModal', false)" class="btn" style="flex: 1; background: var(--bg-input); color: var(--text-main); border: 1px solid var(--border-color);">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="flex: 2;">Create Member</button>
                    </div>
                </form>
            </div>
        </div>

        <style>
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
    @endif
</div>
