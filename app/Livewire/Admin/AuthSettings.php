<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Livewire\Component;

class AuthSettings extends Component
{
    public $clientId;

    public $clientSecret = '';

    public bool $hasClientSecret = false;

    public $redirectUri;

    public $authMode;

    public $allowedEmailsArray = [];

    public $newEmail = '';

    public $firstUserEmail = '';

    public function mount()
    {
        $this->clientId = Setting::get('google_client_id');
        $this->hasClientSecret = filled(Setting::get('google_client_secret'));
        $this->redirectUri = Setting::get('google_redirect_uri') ?: route('auth.google.callback');
        $this->authMode = Setting::get('auth_mode', 'manual');
        $this->firstUserEmail = Setting::get('google_first_user_email', '');

        $emails = Setting::get('google_allowed_emails', '');
        $this->allowedEmailsArray = array_filter(array_map('trim', explode(',', $emails)));
    }

    public function addEmail()
    {
        $this->validate([
            'newEmail' => 'required|email',
        ]);

        $email = strtolower(trim($this->newEmail));

        if (! in_array($email, $this->allowedEmailsArray, true)) {
            $this->allowedEmailsArray[] = $email;
        }

        $this->newEmail = '';
    }

    public function removeEmail($email)
    {
        if ($email === $this->firstUserEmail) {
            session()->flash('error', __('You cannot remove the first setup user from the allowed list.'));

            return;
        }

        $this->allowedEmailsArray = array_values(array_diff($this->allowedEmailsArray, [$email]));
    }

    public function save()
    {
        $rules = [
            'authMode' => 'required|in:manual,google',
            'allowedEmailsArray' => 'array',
            'allowedEmailsArray.*' => 'email:rfc|max:255',
        ];

        if ($this->authMode === 'google') {
            $rules['clientId'] = 'required|string|max:255';
            $rules['redirectUri'] = 'required|url|max:2048';
            $rules['clientSecret'] = $this->hasClientSecret
                ? 'nullable|string|max:2048'
                : 'required|string|max:2048';
        }

        $this->validate($rules);

        Setting::set('google_client_id', $this->clientId, 'auth');
        if (filled($this->clientSecret)) {
            Setting::set('google_client_secret', $this->clientSecret, 'auth');
            $this->hasClientSecret = true;
            $this->clientSecret = '';
        }
        Setting::set('google_redirect_uri', $this->redirectUri, 'auth');
        Setting::set('auth_mode', $this->authMode, 'auth');
        Setting::set('google_allowed_emails', implode(',', $this->allowedEmailsArray), 'auth');

        session()->flash('message', __('Authentication settings updated successfully.'));
    }

    public function render()
    {
        return view('livewire.admin.auth-settings');
    }
}
