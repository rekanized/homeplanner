<div class="setup-container">
    <div class="setup-card">
        <div class="setup-header">
            <h1>{{ __('Home Planner') }}</h1>
            <p>{{ __('Welcome! Let\'s get your household planner ready.') }}</p>
        </div>

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
                    <label>{{ __('Full Name') }}</label>
                    <input type="text" wire:model="name" placeholder="{{ __('E.g. Magnus Andersson') }}">
                    @error('name') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>{{ __('Email Address') }}</label>
                    <input type="email" wire:model="email" placeholder="{{ __('magnus@example.com') }}">
                    @error('email') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>{{ __('Password') }}</label>
                    <input type="password" wire:model="password" placeholder="••••••••">
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
                    <label>{{ __('Client ID') }}</label>
                    <input type="text" wire:model="clientId" placeholder="{{ __('12345678-abcdef...') }}">
                    @error('clientId') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>{{ __('Client Secret') }}</label>
                    <input type="password" wire:model="clientSecret" placeholder="{{ __('GOCSPX-...') }}">
                    @error('clientSecret') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>{{ __('Redirect URI') }}</label>
                    <input type="text" wire:model="redirectUri" readonly>
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
