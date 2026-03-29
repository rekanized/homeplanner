<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test public routes are accessible or redirect to setup if first install.
     */
    public function test_public_routes_redirect_to_setup_on_fresh_install()
    {
        // On a fresh install (0 users), login should redirect to setup
        $this->get(route('login'))->assertRedirect(route('setup.index'));
        $this->get(route('setup.index'))->assertStatus(200);
    }

    /**
     * Test that protected routes redirect to login when unauthenticated.
     */
    public function test_protected_routes_redirect_to_login()
    {
        $routes = [
            'home',
            'economy.index',
            'economy.savings',
            'economy.history',
            'economy.savings-history',
            'shopping.index',
            'todo.index',
            'kids.index',
            'admin.users',
            'admin.logs',
            'admin.versions',
        ];

        foreach ($routes as $routeName) {
            $this->get(route($routeName))->assertRedirect(route('login'));
        }
    }

    /**
     * Test that authenticated users can access general modules.
     */
    public function test_authenticated_users_can_access_general_modules()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $routes = [
            'home',
            'economy.index',
            'economy.savings',
            'economy.history',
            'economy.savings-history',
            'shopping.index',
            'todo.index',
            'kids.index',
        ];

        foreach ($routes as $routeName) {
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertStatus(200);
        }
    }

    /**
     * Test that only admins can access admin routes.
     */
    public function test_only_admins_can_access_admin_routes()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $admin = User::factory()->create(['is_admin' => true]);

        $adminRoutes = [
            'admin.users',
            'admin.logs',
            'admin.versions',
        ];

        foreach ($adminRoutes as $routeName) {
            // regular user blocked
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertStatus(403);

            // admin user allowed
            $this->actingAs($admin)
                ->get(route($routeName))
                ->assertStatus(200);
        }
    }
}
