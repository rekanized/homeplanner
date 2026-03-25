<div class="setup-container">
    <div class="setup-card">
        <div class="setup-header">
            <h1>Home Planner</h1>
            <p>Welcome! Let's get your household planner ready.</p>
        </div>

        @if($step === 1)
            <div class="setup-choices">
                <button wire:click="selectType('google')" class="setup-choice-btn">
                    <div class="choice-icon">G</div>
                    <div class="choice-text">
                        <strong>Google Authentication</strong>
                        <span>Seamless login for your household using Google.</span>
                    </div>
                </button>

                <button wire:click="selectType('manual')" class="setup-choice-btn">
                    <div class="choice-icon">M</div>
                    <div class="choice-text">
                        <strong>Manual Accounts</strong>
                        <span>Create and manage users manually.</span>
                    </div>
                </button>
            </div>
        @elseif($step === 2 && $type === 'manual')
            <form wire:submit.prevent="completeManual" class="setup-form">
                <h2>Manual Administrator Setup</h2>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" wire:model="name" placeholder="E.g. Magnus Andersson">
                    @error('name') <span class="error">{{ $message }}</span> @enderror
                </div>
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
                    <button type="button" wire:click="$set('step', 1)" class="btn-secondary">Back</button>
                    <button type="submit" class="btn-primary">Create Account & Start</button>
                </div>
            </form>
        @elseif($step === 2 && $type === 'google')
            <form wire:submit.prevent="verifyGoogle" class="setup-form">
                <h2>Configure Google OAuth</h2>
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">
                    Obtain these from the <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>.
                </p>
                <div class="form-group">
                    <label>Client ID</label>
                    <input type="text" wire:model="clientId" placeholder="12345678-abcdef...">
                    @error('clientId') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Client Secret</label>
                    <input type="password" wire:model="clientSecret" placeholder="GOCSPX-...">
                    @error('clientSecret') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Redirect URI</label>
                    <input type="text" wire:model="redirectUri" readonly>
                    @error('redirectUri') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="setup-actions">
                    <button type="button" wire:click="$set('step', 1)" class="btn-secondary">Back</button>
                    <button type="submit" class="btn-primary">Verify & Login with Google</button>
                </div>
            </form>
        @endif
    </div>

</div>
