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

    <style>
        .setup-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 20px;
            font-family: 'Outfit', sans-serif;
        }
        .setup-card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.06);
            width: 100%;
            max-width: 500px;
        }
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .setup-header h1 {
            font-size: 2rem;
            font-weight: 900;
            color: var(--text-main);
            margin-bottom: 8px;
        }
        .setup-header p {
            color: var(--text-muted);
            font-weight: 500;
        }
        .setup-choices {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .setup-choice-btn {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            border-radius: 16px;
            border: 2px solid var(--slate-100);
            background: white;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s ease;
        }
        .setup-choice-btn:hover {
            border-color: var(--primary);
            background: var(--primary-soft);
            transform: translateY(-2px);
        }
        .choice-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 900;
        }
        .choice-text strong {
            display: block;
            font-size: 1.1rem;
            color: var(--text-main);
        }
        .choice-text span {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .setup-form h2 {
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: 20px;
            color: var(--text-main);
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--text-main);
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid var(--slate-200);
            font-family: inherit;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-soft);
        }
        .error {
            display: block;
            font-size: 0.75rem;
            color: var(--danger);
            margin-top: 4px;
            font-weight: 600;
        }
        .setup-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .btn-primary {
            flex: 2;
            padding: 12px;
            border-radius: 12px;
            background: var(--primary);
            color: white;
            border: none;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-secondary {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            background: var(--slate-100);
            color: var(--text-main);
            border: none;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-primary:hover { opacity: 0.9; }
    </style>
</div>
