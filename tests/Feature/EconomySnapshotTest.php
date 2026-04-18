<?php

namespace Tests\Feature;

use App\Models\EconomySnapshot;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Saving;
use App\Models\Setting;
use App\Services\EconomySnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EconomySnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_captures_current_state_properly()
    {
        // 1. Setup Data
        Income::create(['name' => 'Salary', 'amount' => 5000, 'sort_order' => 1]);
        Income::create(['name' => 'Bonus', 'amount' => 1000, 'sort_order' => 2]);
        
        Expense::create(['name' => 'Rent', 'amount' => 1500, 'category' => 'Housing', 'sort_order' => 1]);
        Saving::create(['name' => 'Emergency Fund', 'amount' => 500, 'location' => 'Bank A', 'sort_order' => 1]);

        $service = new EconomySnapshotService();
        $snapshot = $service->capture();

        // 2. Assertions
        $this->assertInstanceOf(EconomySnapshot::class, $snapshot);
        $this->assertEquals(6000, $snapshot->total_income);
        $this->assertEquals(1500, $snapshot->total_expenses);
        $this->assertEquals(500, $snapshot->total_savings);
        
        $data = $snapshot->snapshot_data;
        $this->assertCount(2, $data['incomes']);
        $this->assertCount(1, $data['expenses']);
        $this->assertCount(1, $data['savings']);
    }

    public function test_service_removes_one_time_expenses_after_snapshot_is_saved()
    {
        $keptExpense = Expense::create([
            'name' => 'Rent',
            'amount' => 1500,
            'category' => 'Housing',
            'one_time_fee' => false,
            'sort_order' => 1,
        ]);

        $removedExpense = Expense::create([
            'name' => 'Setup Fee',
            'amount' => 300,
            'category' => 'Utilities',
            'one_time_fee' => true,
            'sort_order' => 2,
        ]);

        $service = new EconomySnapshotService();
        $snapshot = $service->capture();

        $this->assertEquals(1800, $snapshot->total_expenses);
        $this->assertCount(2, $snapshot->snapshot_data['expenses']);
        $this->assertSame($removedExpense->id, $snapshot->snapshot_data['expenses'][1]['id']);
        $this->assertTrue($snapshot->snapshot_data['expenses'][1]['one_time_fee']);

        $this->assertDatabaseHas('expenses', ['id' => $keptExpense->id]);
        $this->assertDatabaseMissing('expenses', ['id' => $removedExpense->id]);
    }

    public function test_command_respects_the_configured_snapshot_day()
    {
        // Set snapshot day to 25th
        Setting::set('economy', 'economy_snapshot_day', 25);
        
        // Mock current date to 24th
        $this->travelTo(now()->setDay(24));
        
        $this->artisan('economy:capture-snapshot')
             ->expectsOutputToContain('Today is not the snapshot day')
             ->assertExitCode(0);

        $this->assertDatabaseEmpty('economy_snapshots');

        // Mock current date to 25th
        $this->travelTo(now()->setDay(25));
        
        $this->artisan('economy:capture-snapshot')
             ->expectsOutputToContain('Economy snapshot captured successfully')
             ->assertExitCode(0);

        $this->assertDatabaseCount('economy_snapshots', 1);
    }

    public function test_command_prevents_duplicate_snapshots_in_same_month()
    {
        Setting::set('economy', 'economy_snapshot_day', 25);
        $this->travelTo(now()->setDay(25));

        // First run
        $this->artisan('economy:capture-snapshot');
        $this->assertDatabaseCount('economy_snapshots', 1);

        // Second run same day
        $this->artisan('economy:capture-snapshot')
             ->expectsOutputToContain('Snapshot already exists')
             ->assertExitCode(0);

        $this->assertDatabaseCount('economy_snapshots', 1);

        // Forced run
        $this->artisan('economy:capture-snapshot --force')
             ->expectsOutputToContain('Economy snapshot captured successfully')
             ->assertExitCode(0);

        $this->assertDatabaseCount('economy_snapshots', 2);
    }
}
