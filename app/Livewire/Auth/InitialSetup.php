<?php

namespace App\Livewire\Auth;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class InitialSetup extends Component
{
    public $step = 1;

    public $type = null;

    // Manual setup fields
    public $name = '';

    public $email = '';

    public $password = '';

    // Google setup fields
    public $clientId = '';

    public $clientSecret = '';

    public $redirectUri = '';

    public function selectType($type)
    {
        abort_unless(in_array($type, ['manual', 'google'], true), 422);

        $this->type = $type;
        $this->step = 2;

        if ($type === 'google') {
            $this->redirectUri = route('auth.google.callback');
        }
    }

    public function completeManual()
    {
        abort_if(User::exists(), 403);

        $this->email = is_string($this->email) ? Str::lower(trim($this->email)) : $this->email;

        $this->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email:rfc|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:255',
        ]);

        $user = DB::transaction(function () {
            abort_if(User::lockForUpdate()->exists(), 403);

            return User::create([
                'name' => trim($this->name),
                'email' => Str::lower(trim($this->email)),
                'password' => Hash::make($this->password),
                'is_admin' => true,
            ]);
        });

        Auth::login($user, true);
        session()->regenerate();

        return redirect()->to('/');
    }

    public function verifyGoogle()
    {
        abort_if(User::exists(), 403);

        $this->validate([
            'clientId' => 'required|string|max:255',
            'clientSecret' => 'required|string|max:2048',
            'redirectUri' => 'required|url|max:2048',
        ]);

        // Save credentials
        Setting::set('google_client_id', $this->clientId, 'auth');
        Setting::set('google_client_secret', $this->clientSecret, 'auth');
        Setting::set('google_redirect_uri', $this->redirectUri, 'auth');
        Setting::set('auth_mode', 'google', 'auth');

        // Trigger redirect
        return redirect()->route('auth.google.redirect');
    }

    public function render()
    {
        return view('livewire.auth.initial-setup')
            ->layout('layouts.guest');
    }
}
