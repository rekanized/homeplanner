<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        $clientId = Setting::get('google_client_id');
        $redirectUri = Setting::get('google_redirect_uri');

        if (!$clientId || !$redirectUri) {
            return redirect()->route('setup.index')->with('error', 'Google OAuth not configured.');
        }

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => Str::random(40),
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function callback(Request $request)
    {
        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('setup.index')->with('error', 'Google Auth failed (no code).');
        }

        $clientId = Setting::get('google_client_id');
        $clientSecret = Setting::get('google_client_secret');
        $redirectUri = Setting::get('google_redirect_uri');

        // Exchange code for token
        $response = Http::post('https://oauth2.googleapis.com/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ]);

        if ($response->failed()) {
            return redirect()->route('setup.index')->with('error', 'Failed to exchange token.');
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'];

        // Get User Info
        $userResponse = Http::withToken($accessToken)->get('https://openidconnect.googleapis.com/v1/userinfo');

        if ($userResponse->failed()) {
            return redirect()->route('setup.index')->with('error', 'Failed to get user info.');
        }

        $googleUser = $userResponse->json();
        $email = $googleUser['email'];

        // Enforce whitelist check
        $allowedEmails = Setting::get('google_allowed_emails', '');
        $noAdminsExist = User::where('is_admin', true)->count() === 0;
        $isFirstUser = $noAdminsExist || User::count() === 0;

        if ($isFirstUser) {
            // Store who the first user is so we can protect them from deletion in settings
            // Only update if not already set to avoid overwriting master on subsequent logins if no other admins promoted
            if (!Setting::get('google_first_user_email')) {
                Setting::set('google_first_user_email', $email, 'auth');
            }

            // Automatically add them to the allowed list if not already there
            $allowedArray = array_filter(array_map('trim', explode(',', $allowedEmails)));
            if (!in_array($email, $allowedArray)) {
                $allowedArray[] = $email;
                Setting::set('google_allowed_emails', implode(',', $allowedArray), 'auth');
            }
        } else {
            $allowedArray = array_filter(array_map('trim', explode(',', $allowedEmails)));
            // Only enforce if list is NOT empty OR if user does not already exist
            if (!empty($allowedArray) && !in_array($email, $allowedArray) && !User::where('email', $email)->exists()) {
                return redirect()->route('login')->with('error', 'Your email is not on the allowed list for this household.');
            }
        }

        // sub, name, given_name, family_name, picture, email, email_verified
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $googleUser['name'] ?? ($googleUser['given_name'] . ' ' . $googleUser['family_name']),
                'google_id' => $googleUser['sub'],
                'avatar' => $googleUser['picture'] ?? null,
                'email_verified_at' => now(),
                'is_admin' => $isFirstUser ? true : (User::where('email', $email)->value('is_admin') ?? false),
            ]
        );

        Auth::login($user);

        return redirect()->to('/');
    }
}
