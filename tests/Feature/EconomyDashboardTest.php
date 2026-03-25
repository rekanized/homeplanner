<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Saving;
use App\Models\ExpenseCategory;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EconomyDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user for acting
        $this->user = User::factory()->create(['is_admin' => true]);
    }

    public function test_dashboard_is_accessible_to_authenticated_users()
    {
        $this->actingAs($this->user)
            ->get(route('economy.index'))
            ->assertStatus(200);
    }

    public function test_can_add_expense_row()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Economy\EconomyManager::class)
            ->call('addExpenseRow');

        $this->assertEquals(1, Expense::count());
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Expense::class,
            'event' => 'created'
        ]);
    }

    public function test_can_update_expense_inline()
    {
        $expense = Expense::factory()->create(['name' => 'Old Name']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Economy\EconomyManager::class)
            ->call('updateExpense', $expense->id, 'name', 'New Name');

        $this->assertEquals('New Name', $expense->refresh()->name);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Expense::class,
            'event' => 'updated'
        ]);
    }

    public function test_invalid_updates_are_ignored()
    {
        $expense = Expense::factory()->create(['name' => 'Should Not Change']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Economy\EconomyManager::class)
            // Empty name should be ignored by our hardened logic
            ->call('updateExpense', $expense->id, 'name', '   ');

        $this->assertEquals('Should Not Change', $expense->refresh()->name);
    }

    public function test_can_reorder_expenses()
    {
        $e1 = Expense::factory()->create(['sort_order' => 1]);
        $e2 = Expense::factory()->create(['sort_order' => 2]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Economy\EconomyManager::class)
            ->call('reorder', 'expense', [$e2->id, $e1->id]);

        $this->assertEquals(0, $e2->refresh()->sort_order);
        $this->assertEquals(1, $e1->refresh()->sort_order);
    }

    public function test_audit_logs_are_generated_for_every_significant_action()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Economy\EconomyManager::class)
            ->call('addExpenseCategoryRow');

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => ExpenseCategory::class,
            'event' => 'created'
        ]);
    }
}
