<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\Auth\Login;

class ManualLoginAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_login_form_is_present_when_auth_mode_is_google(): void
    {
        // Create at least one user to avoid redirect to /setup
        User::factory()->create();

        // Set auth_mode to google
        Setting::set('auth_mode', 'google', 'auth');

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Sign in with Google');
        $response->assertSee('Email Address');
        $response->assertSee('Password');
        $response->assertSee('Keep me signed in');
        $response->assertSee('Sign In');
    }

    public function test_manual_login_form_is_present_when_auth_mode_is_manual(): void
    {
        // Create at least one user to avoid redirect to /setup
        User::factory()->create();

        // Set auth_mode to manual
        Setting::set('auth_mode', 'manual', 'auth');

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('Sign in with Google');
        $response->assertSee('Email Address');
        $response->assertSee('Password');
        $response->assertSee('Keep me signed in');
        $response->assertSee('Sign In');
    }

    public function test_manual_login_works(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_manual_login_sets_remember_token_by_default(): void
    {
        $user = User::factory()->create([
            'email' => 'remembered@example.com',
            'password' => bcrypt('password123'),
            'remember_token' => null,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'remembered@example.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_manual_login_can_skip_persistent_login(): void
    {
        $user = User::factory()->create([
            'email' => 'session-only@example.com',
            'password' => bcrypt('password123'),
            'remember_token' => null,
        ]);

        Livewire::test(Login::class)
            ->set('email', 'session-only@example.com')
            ->set('password', 'password123')
            ->set('remember', false)
            ->call('login')
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->fresh()->remember_token);
    }
}
