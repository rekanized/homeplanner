<div class="card">
    <div class="card-header">
        <h3 style="font-weight: 900;">Authentication Settings</h3>
        <p style="font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Configure how users sign in</p>
    </div>
    <div class="card-body">
        @if (session()->has('message'))
            <div style="background: var(--success-soft); color: var(--success); padding: 12px; border-radius: 12px; margin-bottom: 20px; font-weight: 700; font-size: 13px;">
                {{ session('message') }}
            </div>
        @endif

        <form wire:submit.prevent="save" style="display: flex; flex-direction: column; gap: 20px;">
            <div class="form-group-flex" style="display: flex; gap: 20px;">
                <div style="flex: 1;">
                    <label style="display: block; font-size: 12px; font-weight: 800; margin-bottom: 8px; color: var(--slate-500); text-transform: uppercase;">Auth Mode</label>
                    <select wire:model="authMode" class="eco-inline-select" style="width: 100%; height: 44px; border: 1px solid var(--border-color); background: var(--bg-input);">
                        <option value="manual">Manual (Username/Password)</option>
                        <option value="google">Google OAuth</option>
                    </select>
                </div>
            </div>

            <div x-show="$wire.authMode === 'google'" x-transition>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 800; margin-bottom: 8px; color: var(--slate-500); text-transform: uppercase;">Google Client ID</label>
                        <input type="text" wire:model="clientId" class="eco-inline-input" style="border: 1px solid var(--border-color); background: var(--bg-input); height: 44px;" placeholder="12345678-abcdef...">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 800; margin-bottom: 8px; color: var(--slate-500); text-transform: uppercase;">Google Client Secret</label>
                        <input type="password" wire:model="clientSecret" class="eco-inline-input" style="border: 1px solid var(--border-color); background: var(--bg-input); height: 44px;" placeholder="GOCSPX-...">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 800; margin-bottom: 8px; color: var(--slate-500); text-transform: uppercase;">Redirect URI</label>
                        <input type="text" wire:model="redirectUri" class="eco-inline-input" style="border: 1px solid var(--border-color); background: var(--bg-input); height: 44px; color: var(--text-muted);" readonly>
                        <p style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">Whitelist this URL in your Google Cloud Console.</p>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 800; margin-bottom: 8px; color: var(--slate-500); text-transform: uppercase;">Allowed Gmail Accounts</label>
                        
                        <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                            <input type="email" wire:model="newEmail" wire:keydown.enter.prevent="addEmail" class="eco-inline-input" style="border: 1px solid var(--border-color); background: var(--bg-input); height: 44px; flex: 1;" placeholder="Enter email address...">
                            <button type="button" wire:click="addEmail" class="btn-primary" style="padding: 0 20px; border-radius: 12px; height: 44px; font-size: 13px;">Add User</button>
                        </div>
                        @error('newEmail') <p style="font-size: 11px; color: var(--danger); margin-bottom: 12px; font-weight: 700;">{{ $message }}</p> @enderror

                        <div style="display: flex; flex-wrap: wrap; gap: 8px; padding: 16px; background: var(--bg-input); border: 1px dashed var(--border-color); border-radius: 16px; min-height: 60px;">
                            @forelse($allowedEmailsArray as $email)
                                <div style="display: flex; align-items: center; gap: 8px; background: var(--bg-card); padding: 6px 12px; border-radius: 100px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                                    <span style="font-size: 13px; font-weight: 700; color: var(--text-main);">
                                        {{ $email }}
                                        @if($email === $firstUserEmail)
                                            <span style="font-size: 9px; background: var(--primary); color: white; padding: 2px 6px; border-radius: 6px; margin-left: 4px; vertical-align: middle;">MASTER</span>
                                        @endif
                                    </span>
                                    
                                    @if($email !== $firstUserEmail)
                                        <button type="button" wire:click="removeEmail('{{ $email }}')" style="background: var(--danger-soft); color: var(--danger); border: none; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; font-weight: 900;">×</button>
                                    @else
                                        <div style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; color: var(--text-muted); opacity: 0.5;">🔒</div>
                                    @endif
                                </div>
                            @empty
                                <p style="font-size: 12px; color: var(--text-muted); font-weight: 600;">No specific accounts restricted. Anyone with a valid Gmail account in your household can sign in.</p>
                            @endforelse
                        </div>
                        @if (session()->has('error'))
                            <p style="font-size: 11px; color: var(--danger); margin-top: 8px; font-weight: 700;">{{ session('error') }}</p>
                        @endif
                        <p style="font-size: 10px; color: var(--text-muted); margin-top: 8px;">Only the users listed above will be allowed to sign in via Google OAuth.</p>
                    </div>
                </div>
            </div>

            <div style="margin-top: 10px; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn-primary" style="padding: 10px 24px; border-radius: 12px; background: var(--primary); color: white; border: none; font-weight: 800; cursor: pointer;">
                    Save Authentication Settings
                </button>
            </div>
        </form>
    </div>
</div>
