<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleAuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_stores_state_in_the_session(): void
    {
        User::factory()->create();
        Setting::set('auth_mode', 'google', 'auth');
        Setting::set('google_client_id', 'client-id', 'auth');
        Setting::set('google_redirect_uri', route('auth.google.callback'), 'auth');

        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirectContains('accounts.google.com/o/oauth2/v2/auth');
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertNotEmpty($query['state'] ?? null);
        $this->assertSame($query['state'], session('google_oauth_state'));
    }

    public function test_google_callback_rejects_an_invalid_state_without_contacting_google(): void
    {
        User::factory()->create();
        Http::fake();

        $this->withSession(['google_oauth_state' => 'expected-state'])
            ->get(route('auth.google.callback', ['code' => 'code', 'state' => 'wrong-state']))
            ->assertRedirect(route('login'));

        Http::assertNothingSent();
    }

    public function test_google_callback_uses_form_encoding_and_accepts_an_allowed_verified_user(): void
    {
        User::factory()->create(['is_admin' => true]);
        Setting::set('auth_mode', 'google', 'auth');
        Setting::set('google_client_id', 'client-id', 'auth');
        Setting::set('google_client_secret', 'client-secret', 'auth');
        Setting::set('google_redirect_uri', route('auth.google.callback'), 'auth');
        Setting::set('google_allowed_emails', 'member@example.com', 'auth');

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'access-token']),
            'openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'sub' => 'google-user-id',
                'email' => 'member@example.com',
                'email_verified' => true,
                'name' => 'Household Member',
            ]),
        ]);

        $this->withSession(['google_oauth_state' => 'valid-state'])
            ->get(route('auth.google.callback', ['code' => 'auth-code', 'state' => 'valid-state']))
            ->assertRedirect('/');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'member@example.com', 'google_id' => 'google-user-id']);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://oauth2.googleapis.com/token'
            && str_starts_with($request->header('Content-Type')[0] ?? '', 'application/x-www-form-urlencoded')
            && $request['client_secret'] === 'client-secret');
    }
}
