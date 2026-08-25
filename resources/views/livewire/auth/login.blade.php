<div class="setup-container">
    <div class="setup-card">
        <div class="setup-header">
            <h1>{{ __('Home Planner') }}</h1>
            <p>{{ __('Please sign in to continue.') }}</p>
        </div>

        @if(session('error'))
            <div class="error" role="alert" style="margin-bottom: 20px; padding: 12px 14px; border-radius: 12px; background: var(--danger-soft);">
                {{ session('error') }}
            </div>
        @endif

        @if($authMode === 'google')
            <div class="setup-choices">
                <button wire:click="loginWithGoogle" class="setup-choice-btn" style="justify-content: center;">
                    <div class="choice-icon">G</div>
                    <div class="choice-text">
                        <strong style="font-size: 1.25rem;">{{ __('Sign in with Google') }}</strong>
                    </div>
                </button>
            </div>

            <div style="margin: 24px 0; display: flex; align-items: center; gap: 16px; color: var(--text-muted);">
                <div style="flex: 1; height: 1px; background: var(--border-color);"></div>
                <span style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('OR') }}</span>
                <div style="flex: 1; height: 1px; background: var(--border-color);"></div>
            </div>
        @endif

        <form wire:submit.prevent="login" class="setup-form">
            <div class="form-group">
                <label for="login-email">{{ __('Email Address') }}</label>
                <input id="login-email" type="email" wire:model="email" autocomplete="email" placeholder="{{ __('magnus@example.com') }}">
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="login-password">{{ __('Password') }}</label>
                <input id="login-password" type="password" wire:model="password" autocomplete="current-password" placeholder="••••••••">
                @error('password') <span class="error">{{ $message }}</span> @enderror
            </div>
            <label style="display: flex; align-items: center; gap: 10px; font-size: 0.95rem; color: var(--text-secondary); margin-bottom: 20px; cursor: pointer;">
                <input type="checkbox" wire:model="remember">
                <span>{{ __('Keep me signed in') }}</span>
            </label>
            <div class="setup-actions">
                <button type="submit" class="btn-primary" style="flex: 1;">{{ __('Sign In') }}</button>
            </div>
        </form>
        
        <p style="text-align: center; margin-top: 24px; font-size: 12px; color: var(--text-muted);">
            {{ __('If you need access, contact the household administrator.') }}
        </p>
    </div>
</div>
