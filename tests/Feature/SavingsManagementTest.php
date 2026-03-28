<?php

namespace Tests\Feature;

use App\Livewire\Economy\Savings;
use App\Models\SavingsBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SavingsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_savings_component_can_add_rows()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(Savings::class)
            ->call('addSavingRow');

        $this->assertDatabaseCount('savings_balances', 1);
    }

    public function test_savings_component_can_update_fields()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $balance = SavingsBalance::create([
            'name' => 'Original Name',
            'amount' => 1000,
            'location' => 'Bank A',
            'sort_order' => 1
        ]);

        Livewire::test(Savings::class)
            ->call('updateSaving', $balance->id, 'name', 'New Account')
            ->call('updateSaving', $balance->id, 'amount', 2500);

        $this->assertDatabaseHas('savings_balances', [
            'id' => $balance->id,
            'name' => 'New Account',
            'amount' => 2500
        ]);
    }

    public function test_savings_component_can_delete_rows()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $balance = SavingsBalance::create([
            'name' => 'To Delete',
            'amount' => 100,
            'sort_order' => 1
        ]);

        Livewire::test(Savings::class)
            ->call('deleteSaving', $balance->id);

        $this->assertDatabaseMissing('savings_balances', ['id' => $balance->id]);
    }

    public function test_savings_component_calculates_total_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        SavingsBalance::create(['name' => 'A', 'amount' => 1000, 'sort_order' => 1]);
        SavingsBalance::create(['name' => 'B', 'amount' => 2000, 'sort_order' => 2]);

        Livewire::test(Savings::class)
            ->assertSee('3 000');
    }
}
