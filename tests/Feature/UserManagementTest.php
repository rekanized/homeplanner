<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@example.com']);
        Setting::set('google_first_user_email', 'master@example.com');
    }

    public function test_can_create_manual_user()
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserList::class)
            ->set('name', 'New Member')
            ->set('email', 'new@example.com')
            ->set('password', 'password123')
            ->call('createUser');

        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    public function test_cannot_delete_self()
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserList::class)
            ->call('deleteUser', $this->admin->id)
            ->assertSee('You cannot delete your own account.');
            
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_cannot_delete_master_user()
    {
        $master = User::factory()->create(['email' => 'master@example.com']);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserList::class)
            ->call('deleteUser', $master->id)
            ->assertSee('The Master User cannot be deleted.');
            
        $this->assertDatabaseHas('users', ['id' => $master->id]);
    }

    public function test_can_delete_regular_user()
    {
        $user = User::factory()->create(['email' => 'regular@example.com']);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\UserList::class)
            ->call('deleteUser', $user->id)
            ->assertSee('User deleted successfully.');
            
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
