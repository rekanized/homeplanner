<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_users_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.users'))
            ->assertStatus(403);

        $this->actingAs($user)
            ->get(route('admin.logs'))
            ->assertStatus(403);
    }

    public function test_admin_users_can_access_admin_routes()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get(route('admin.logs'))
            ->assertStatus(200);
    }

    public function test_unauthenticated_users_are_redirected_to_login()
    {
        $this->get(route('admin.users'))
            ->assertRedirect(route('login'));
    }
}
