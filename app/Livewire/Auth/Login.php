<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $authMode = 'manual';

    public function mount()
    {
        if (Auth::check()) {
            return redirect()->intended('/');
        }

        $this->authMode = Setting::get('auth_mode', 'manual');
    }

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Too many login attempts. Please try again in {$seconds} seconds.");
            return;
        }

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            RateLimiter::clear($throttleKey);
            session()->regenerate();
            return redirect()->intended('/');
        }

        RateLimiter::hit($throttleKey);
        $this->addError('email', 'The provided credentials do not match our records.');
    }

    public function loginWithGoogle()
    {
        return redirect()->route('auth.google.redirect');
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.guest');
    }
}
