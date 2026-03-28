<?php

namespace Tests\Feature;

use App\Livewire\Economy\SavingsHistory;
use App\Models\SavingsBalance;
use App\Models\SavingsSnapshot;
use App\Models\User;
use App\Services\EconomySnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SavingsSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_captures_savings_snapshot_correctly()
    {
        // 1. Setup Data
        SavingsBalance::create(['name' => 'Bank account', 'amount' => 1000, 'location' => 'Bank A', 'sort_order' => 1]);
        SavingsBalance::create(['name' => 'Stocks', 'amount' => 5000, 'location' => 'Avanza', 'sort_order' => 2]);
        
        $service = new EconomySnapshotService();
        $snapshot = $service->captureSavingsSnapshot();

        // 2. Assertions
        $this->assertInstanceOf(SavingsSnapshot::class, $snapshot);
        $this->assertEquals(6000, $snapshot->total_amount);
        $this->assertCount(2, $snapshot->snapshot_data);
        $this->assertEquals('Bank account', $snapshot->snapshot_data[0]['name']);
    }

    public function test_history_component_displays_latest_snapshot_initially()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $s1 = SavingsSnapshot::create(['month' => 1, 'year' => 2026, 'total_amount' => 100, 'snapshot_data' => [], 'created_at' => now()->subDay()]);
        $s2 = SavingsSnapshot::create(['month' => 2, 'year' => 2026, 'total_amount' => 200, 'snapshot_data' => [], 'created_at' => now()]);

        Livewire::test(SavingsHistory::class)
            ->assertSet('selectedSnapshotId', $s2->id);
    }

    public function test_history_component_can_trigger_snapshot_manually()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        SavingsBalance::create(['name' => 'Emergency Fund', 'amount' => 100, 'sort_order' => 1]);

        Livewire::test(SavingsHistory::class)
            ->call('triggerManualSnapshot')
            ->assertSee('Savings snapshot captured successfully.');

        $this->assertDatabaseCount('savings_snapshots', 1);
    }

    public function test_history_component_can_delete_snapshot()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $s1 = SavingsSnapshot::create(['month' => 1, 'year' => 2026, 'total_amount' => 100, 'snapshot_data' => []]);

        Livewire::test(SavingsHistory::class)
            ->call('deleteSnapshot', $s1->id);

        $this->assertDatabaseMissing('savings_snapshots', ['id' => $s1->id]);
    }

    public function test_command_triggers_savings_snapshot_correctly()
    {
        // Set snapshot day to current day
        $this->travelTo(now()->setDay(25));
        \App\Models\Setting::set('economy', 'economy_snapshot_day', 25);
        
        SavingsBalance::create(['name' => 'A', 'amount' => 1000, 'sort_order' => 1]);

        // Mock Command run
        $this->artisan('economy:capture-snapshot')
             ->expectsOutputToContain('Economy snapshot captured successfully')
             ->expectsOutputToContain('Savings snapshot captured successfully')
             ->assertExitCode(0);

        $this->assertDatabaseCount('savings_snapshots', 1);
        $this->assertDatabaseCount('economy_snapshots', 1);
    }
}
