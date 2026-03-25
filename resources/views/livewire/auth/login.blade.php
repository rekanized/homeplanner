<div class="setup-container">
    <div class="setup-card">
        <div class="setup-header">
            <h1>Home Planner</h1>
            <p>Please sign in to continue.</p>
        </div>

        @if($authMode === 'google')
            <div class="setup-choices">
                <button wire:click="loginWithGoogle" class="setup-choice-btn" style="justify-content: center;">
                    <div class="choice-icon">G</div>
                    <div class="choice-text">
                        <strong style="font-size: 1.25rem;">Sign in with Google</strong>
                    </div>
                </button>
            </div>

            <div style="margin: 24px 0; display: flex; align-items: center; gap: 16px; color: var(--text-muted);">
                <div style="flex: 1; height: 1px; background: var(--border-color);"></div>
                <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">OR</span>
                <div style="flex: 1; height: 1px; background: var(--border-color);"></div>
            </div>
        @endif

        <form wire:submit.prevent="login" class="setup-form">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" wire:model="email" placeholder="magnus@example.com">
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" wire:model="password" placeholder="••••••••">
                @error('password') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="setup-actions">
                <button type="submit" class="btn-primary" style="flex: 1;">Sign In</button>
            </div>
        </form>
        
        <p style="text-align: center; margin-top: 24px; font-size: 12px; color: var(--text-muted);">
            If you need access, contact the household administrator.
        </p>
    </div>
</div>
