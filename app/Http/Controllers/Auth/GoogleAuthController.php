<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        if (User::exists() && Setting::get('auth_mode', 'manual') !== 'google') {
            return redirect()->route('login')->with('error', 'Google sign-in is disabled.');
        }

        $clientId = Setting::get('google_client_id');
        $redirectUri = Setting::get('google_redirect_uri');

        if (! $clientId || ! $redirectUri) {
            $route = User::exists() ? 'login' : 'setup.index';

            return redirect()->route($route)->with('error', 'Google OAuth not configured.');
        }

        $state = Str::random(64);
        session()->put('google_oauth_state', $state);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state,
            'prompt' => 'select_account',
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function callback(Request $request)
    {
        if ($request->filled('error')) {
            session()->forget('google_oauth_state');

            return redirect()->route('login')->with('error', 'Google sign-in was cancelled or denied.');
        }

        $expectedState = (string) $request->session()->pull('google_oauth_state', '');
        $providedState = (string) $request->input('state', '');

        if ($expectedState === '' || $providedState === '' || ! hash_equals($expectedState, $providedState)) {
            return redirect()->route('login')->with('error', 'Google sign-in could not be verified. Please try again.');
        }

        if (User::exists() && Setting::get('auth_mode', 'manual') !== 'google') {
            return redirect()->route('login')->with('error', 'Google sign-in is disabled.');
        }

        $code = $request->input('code');
        if (! $code) {
            return redirect()->route('login')->with('error', 'Google sign-in failed (no authorization code).');
        }

        $clientId = Setting::get('google_client_id');
        $clientSecret = Setting::get('google_client_secret');
        $redirectUri = Setting::get('google_redirect_uri');

        // Exchange code for token
        try {
            $response = Http::asForm()->connectTimeout(5)->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
            ]);
        } catch (ConnectionException) {
            return redirect()->route('login')->with('error', 'Google sign-in is temporarily unavailable. Please try again.');
        }

        if ($response->failed()) {
            return redirect()->route('login')->with('error', 'Google sign-in could not be completed.');
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'] ?? null;
        if (! is_string($accessToken) || $accessToken === '') {
            return redirect()->route('login')->with('error', 'Google returned an invalid sign-in response.');
        }

        // Get User Info
        try {
            $userResponse = Http::withToken($accessToken)
                ->connectTimeout(5)
                ->timeout(15)
                ->get('https://openidconnect.googleapis.com/v1/userinfo');
        } catch (ConnectionException) {
            return redirect()->route('login')->with('error', 'Google sign-in is temporarily unavailable. Please try again.');
        }

        if ($userResponse->failed()) {
            return redirect()->route('login')->with('error', 'Google user information could not be retrieved.');
        }

        $googleUser = $userResponse->json();
        $email = Str::lower(trim((string) ($googleUser['email'] ?? '')));
        $googleId = (string) ($googleUser['sub'] ?? '');

        if ($email === '' || $googleId === '' || ($googleUser['email_verified'] ?? false) !== true) {
            return redirect()->route('login')->with('error', 'Google did not provide a verified email address.');
        }

        // Enforce whitelist check
        $allowedEmails = Setting::get('google_allowed_emails', '');
        $noAdminsExist = User::where('is_admin', true)->count() === 0;
        $isFirstUser = $noAdminsExist || User::count() === 0;

        $allowedArray = collect(explode(',', $allowedEmails))
            ->map(fn ($allowedEmail) => Str::lower(trim($allowedEmail)))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $existingUser = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($isFirstUser) {
            // Store who the first user is so we can protect them from deletion in settings
            // Only update if not already set to avoid overwriting master on subsequent logins if no other admins promoted
            if (! Setting::get('google_first_user_email')) {
                Setting::set('google_first_user_email', $email, 'auth');
            }

            // Automatically add them to the allowed list if not already there
            if (! in_array($email, $allowedArray, true)) {
                $allowedArray[] = $email;
                Setting::set('google_allowed_emails', implode(',', $allowedArray), 'auth');
            }
        } else {
            // Existing household members retain access. New accounts must be explicitly allowed.
            if (! $existingUser && ! in_array($email, $allowedArray, true)) {
                return redirect()->route('login')->with('error', 'Your email is not on the allowed list for this household.');
            }
        }

        // sub, name, given_name, family_name, picture, email, email_verified
        $fallbackName = trim(implode(' ', array_filter([
            $googleUser['given_name'] ?? null,
            $googleUser['family_name'] ?? null,
        ])));
        $name = trim((string) ($googleUser['name'] ?? $fallbackName));

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name !== '' ? $name : Str::before($email, '@'),
                'google_id' => $googleId,
                'avatar' => $googleUser['picture'] ?? null,
                'email_verified_at' => now(),
                'is_admin' => $isFirstUser ? true : ($existingUser?->is_admin ?? false),
            ]
        );

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->to('/');
    }
}
