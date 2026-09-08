<div class="setup-container">
    <div class="setup-card">
        <div class="setup-header">
            <x-logo size="64" class="setup-logo" />
            <h1>{{ __('Home Planner') }}</h1>
            <p>{{ __('Welcome! Let\'s get your household planner ready.') }}</p>
        </div>

        @if(session('error'))
            <div class="error" role="alert" style="margin-bottom: 20px; padding: 12px 14px; border-radius: 12px; background: var(--danger-soft);">
                {{ session('error') }}
            </div>
        @endif

        @if($step === 1)
            <div class="setup-choices">
                <button wire:click="selectType('google')" class="setup-choice-btn">
                    <div class="choice-icon">G</div>
                    <div class="choice-text">
                        <strong>{{ __('Google Authentication') }}</strong>
                        <span>{{ __('Seamless login for your household using Google.') }}</span>
                    </div>
                </button>

                <button wire:click="selectType('manual')" class="setup-choice-btn">
                    <div class="choice-icon">M</div>
                    <div class="choice-text">
                        <strong>{{ __('Manual Accounts') }}</strong>
                        <span>{{ __('Create and manage users manually.') }}</span>
                    </div>
                </button>
            </div>
        @elseif($step === 2 && $type === 'manual')
            <form wire:submit.prevent="completeManual" class="setup-form">
                <h2>{{ __('Manual Administrator Setup') }}</h2>
                <div class="form-group">
                    <label for="setup-name">{{ __('Full Name') }}</label>
                    <input id="setup-name" type="text" wire:model="name" autocomplete="name" placeholder="{{ __('E.g. Magnus Andersson') }}">
                    @error('name') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="setup-email">{{ __('Email Address') }}</label>
                    <input id="setup-email" type="email" wire:model="email" autocomplete="email" placeholder="{{ __('magnus@example.com') }}">
                    @error('email') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="setup-password">{{ __('Password') }}</label>
                    <input id="setup-password" type="password" wire:model="password" autocomplete="new-password" placeholder="••••••••">
                    @error('password') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="setup-actions">
                    <button type="button" wire:click="$set('step', 1)" class="btn-secondary">{{ __('Back') }}</button>
                    <button type="submit" class="btn-primary">{{ __('Create Account & Start') }}</button>
                </div>
            </form>
        @elseif($step === 2 && $type === 'google')
            <form wire:submit.prevent="verifyGoogle" class="setup-form">
                <h2>{{ __('Configure Google OAuth') }}</h2>
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">
                    {{ __('Obtain these from the') }} <a href="https://console.cloud.google.com/" target="_blank">{{ __('Google Cloud Console') }}</a>.
                </p>
                <div class="form-group">
                    <label for="setup-client-id">{{ __('Client ID') }}</label>
                    <input id="setup-client-id" type="text" wire:model="clientId" autocomplete="off" placeholder="{{ __('12345678-abcdef...') }}">
                    @error('clientId') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="setup-client-secret">{{ __('Client Secret') }}</label>
                    <input id="setup-client-secret" type="password" wire:model="clientSecret" autocomplete="new-password" placeholder="{{ __('GOCSPX-...') }}">
                    @error('clientSecret') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="setup-redirect-uri">{{ __('Redirect URI') }}</label>
                    <input id="setup-redirect-uri" type="url" wire:model="redirectUri" readonly>
                    @error('redirectUri') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="setup-actions">
                    <button type="button" wire:click="$set('step', 1)" class="btn-secondary">{{ __('Back') }}</button>
                    <button type="submit" class="btn-primary">{{ __('Verify & Login with Google') }}</button>
                </div>
            </form>
        @endif
    </div>

</div>
