<div class="animate-in" style="max-width: 1280px; margin: 0 auto; padding: 40px 24px;">
    <div class="flex-header" style="align-items: flex-end;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 900; color: var(--text-main); font-family: var(--font-heading); margin-bottom: 8px;">Registered Users</h1>
            <p style="color: var(--text-muted); font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em;">Household member registration log</p>
        </div>
        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
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

    @if (session()->has('error'))
        <div style="background: var(--danger-soft); color: var(--danger); padding: 16px; border-radius: 16px; margin-bottom: 24px; font-weight: 700; font-size: 14px; border: 1px solid var(--danger);">
            {{ session('error') }}
        </div>
    @endif

    <div class="card" style="overflow: hidden;">
        <table class="responsive-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-input); border-bottom: 2px solid var(--border-color);">
                    <th style="padding: 16px 24px; text-align: left; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Member</th>
                    <th style="padding: 16px 24px; text-align: left; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Method</th>
                    <th style="padding: 16px 24px; text-align: left; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Joined Date</th>
                    <th style="padding: 16px 24px; text-align: right; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Account Role</th>
                </tr>
            </thead>
            <tbody style="background: var(--bg-card);">
                @foreach($users as $user)
                    <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.2s ease;">
                        <td data-label="Member" style="padding: 20px 24px;">
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
                        <td data-label="Method" style="padding: 20px 24px;">
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
                         <td data-label="Joined Date" style="padding: 20px 24px;">
                            <div style="font-size: 14px; font-weight: 700; color: var(--text-main);">{{ $user->created_at->format('M j, Y') }}</div>
                            <div style="font-size: 12px; font-weight: 500; color: var(--text-muted);">{{ $user->created_at->format('H:i') }}</div>
                        </td>
                        <td data-label="Role" style="padding: 20px 24px; text-align: right;">
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 16px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    @if($user->isMaster())
                                        <span class="badge" style="background: var(--primary); color: white; border: none; font-weight: 900; box-shadow: 0 4px 10px var(--primary-soft);">System Master</span>
                                    @elseif(auth()->user()->isMaster())
                                        <!-- Interactive Role Toggle for Master -->
                                        <div style="display: flex; background: var(--bg-input); padding: 4px; border-radius: 100px; border: 1px solid var(--border-color); position: relative;">
                                            <button wire:click="toggleChild({{ $user->id }})" 
                                                    style="padding: 6px 14px; border-radius: 100px; border: none; cursor: pointer; font-size: 10px; font-weight: 900; text-transform: uppercase; transition: all 0.2s; 
                                                    {{ !$user->is_child ? 'background: white; color: var(--success); box-shadow: var(--shadow-sm);' : 'background: transparent; color: var(--text-muted);' }}">
                                                Member
                                            </button>
                                            <button wire:click="toggleChild({{ $user->id }})" 
                                                    style="padding: 6px 14px; border-radius: 100px; border: none; cursor: pointer; font-size: 10px; font-weight: 900; text-transform: uppercase; transition: all 0.2s; 
                                                    {{ $user->is_child ? 'background: #6366f1; color: white; box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);' : 'background: transparent; color: var(--text-muted);' }}">
                                                Child
                                            </button>
                                        </div>
                                    @else
                                        <!-- Static Badge for others -->
                                        @if($user->is_child)
                                            <span style="display: inline-block; padding: 4px 12px; border-radius: 100px; background: #6366f120; color: #6366f1; font-size: 11px; font-weight: 800; text-transform: uppercase; border: 1px solid #6366f140;">Child</span>
                                        @else
                                            <span style="display: inline-block; padding: 4px 12px; border-radius: 100px; background: var(--success-soft); color: var(--success); font-size: 11px; font-weight: 800; text-transform: uppercase;">Member</span>
                                        @endif
                                    @endif
                                </div>

                                <div style="width: 1px; height: 24px; background: var(--border-color); margin: 0 4px;"></div>

                                <button wire:click="deleteUser({{ $user->id }})" 
                                        wire:confirm="Are you sure you want to remove this member? This will delete all their data logs."
                                        style="background: none; border: none; cursor: pointer; color: var(--danger); opacity: 0.6; transition: opacity 0.2s;"
                                        onmouseover="this.style.opacity='1'" 
                                        onmouseout="this.style.opacity='0.6'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Create Modal -->
    @if($showCreateModal)
        <div class="modal-overlay" @click.self="$wire.set('showCreateModal', false)">
            <div class="modal-content" style="max-width: 450px; text-align: left;">
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
