<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        $this->type = $type;
        $this->step = 2;
        
        if ($type === 'google') {
            $this->redirectUri = route('auth.google.callback');
        }
    }

    public function completeManual()
    {
        $this->validate([
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'is_admin' => true,
        ]);

        Auth::login($user, true);
        session()->regenerate();

        return redirect()->to('/');
    }

    public function verifyGoogle()
    {
        $this->validate([
            'clientId' => 'required',
            'clientSecret' => 'required',
            'redirectUri' => 'required|url',
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
