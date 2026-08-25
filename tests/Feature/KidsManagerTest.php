<?php

namespace Tests\Feature;

use App\Livewire\Kids\KidsManager;
use App\Models\Chore;
use App\Models\PredefinedChore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KidsManagerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the kids manager is accessible to parents/admins.
     */
    public function test_kids_manager_is_accessible()
    {
        $user = User::factory()->create(['is_admin' => true, 'is_child' => false]);

        $this->actingAs($user)
            ->get(route('kids.index'))
            ->assertStatus(200);
    }

    /**
     * Test that a parent can add a chore for a child.
     */
    public function test_can_add_chore_to_child()
    {
        $parent = User::factory()->create(['is_admin' => true]);
        $child = User::factory()->create(['is_child' => true]);

        Livewire::actingAs($parent)
            ->test(KidsManager::class)
            ->set('title', 'Clean the room')
            ->set('score', 20)
            ->set('assigned_to', [$child->id])
            ->call('addChore');

        $this->assertDatabaseHas('chores', [
            'title' => 'Clean the room',
            'user_id' => $child->id,
            'is_completed' => false,
        ]);
    }

    /**
     * Test that completing a chore awards points.
     */
    public function test_completing_chore_awards_points()
    {
        $parent = User::factory()->create(['is_admin' => true]);
        $child = User::factory()->create(['is_child' => true, 'accumulated_score' => 0]);
        $chore = Chore::factory()->create(['user_id' => $child->id, 'score' => 50, 'is_completed' => false]);

        Livewire::actingAs($parent)
            ->test(KidsManager::class)
            ->call('completeChore', $chore->id);

        $this->assertEquals(50, $child->fresh()->accumulated_score);
        $this->assertTrue($chore->fresh()->is_completed);
    }

    /**
     * Test point adjustment by parent.
     */
    public function test_parent_can_adjust_points()
    {
        $parent = User::factory()->create(['is_admin' => true]);
        $child = User::factory()->create(['is_child' => true, 'accumulated_score' => 100]);

        Livewire::actingAs($parent)
            ->test(KidsManager::class)
            ->set('adjustUserId', $child->id)
            ->set('adjustAmount', 25)
            ->set('adjustType', 'remove')
            ->call('adjustPoints');

        $this->assertEquals(75, $child->fresh()->accumulated_score);
    }

    /**
     * Test redeeming points for a reward.
     */
    public function test_can_redeem_points()
    {
        $parent = User::factory()->create(['is_admin' => true]);
        $child = User::factory()->create(['is_child' => true, 'accumulated_score' => 300]);

        Livewire::actingAs($parent)
            ->test(KidsManager::class)
            ->set('redemptionUserId', $child->id)
            ->set('redemptionDescription', 'New Toy')
            ->set('redemptionPoints', 200)
            ->call('usePoints');

        $this->assertEquals(100, $child->fresh()->accumulated_score);
        $this->assertDatabaseHas('redemptions', [
            'user_id' => $child->id,
            'description' => 'New Toy',
            'score' => 200,
        ]);
    }

    /**
     * Test managing chore templates.
     */
    public function test_can_save_chore_template()
    {
        $parent = User::factory()->create(['is_admin' => true]);
        $child = User::factory()->create(['is_child' => true]);

        Livewire::actingAs($parent)
            ->test(KidsManager::class)
            ->set('templateTitle', 'Weekly Vacuuming')
            ->set('templateScore', 40)
            ->set('templateRecurrenceType', 'weekly')
            ->set('templateRecurrenceDay', ['Monday'])
            ->set('templateAssignedUserIds', [$child->id])
            ->call('saveTemplate');

        $this->assertDatabaseHas('predefined_chores', [
            'title' => 'Weekly Vacuuming',
            'score' => 40,
            'recurrence_type' => 'weekly',
        ]);
    }

    public function test_recurring_chore_preserves_approval_requirement(): void
    {
        $child = User::factory()->create(['is_child' => true]);
        PredefinedChore::create([
            'title' => 'Photo Chore',
            'score' => 20,
            'recurrence_type' => 'daily',
            'assigned_user_ids' => [$child->id],
            'needs_approval' => true,
        ]);

        $this->artisan('kids:generate-recurring')->assertExitCode(0);

        $this->assertDatabaseHas('chores', [
            'title' => 'Photo Chore',
            'user_id' => $child->id,
            'needs_approval' => true,
        ]);
    }
}
