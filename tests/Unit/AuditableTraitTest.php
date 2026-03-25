<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Expense;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditableTraitTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_model_creation()
    {
        $user = User::factory()->create(['name' => 'Test User']);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'event' => 'created',
        ]);

        $log = AuditLog::first();
        $this->assertArrayHasKey('name', $log->new_values);
        $this->assertEquals('Test User', $log->new_values['name']);
    }

    public function test_it_logs_model_updates()
    {
        $expense = Expense::factory()->create(['name' => 'Original']);

        $expense->update(['name' => 'Updated']);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Expense::class,
            'auditable_id' => $expense->id,
            'event' => 'updated',
        ]);

        $log = AuditLog::where('event', 'updated')->first();
        $this->assertEquals('Original', $log->old_values['name']);
        $this->assertEquals('Updated', $log->new_values['name']);
    }

    public function test_it_logs_model_deletion()
    {
        $expense = Expense::factory()->create(['name' => 'To be deleted']);

        $expenseId = $expense->id;
        $expense->delete();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Expense::class,
            'auditable_id' => $expenseId,
            'event' => 'deleted',
        ]);

        $log = AuditLog::where('event', 'deleted')->first();
        $this->assertEquals('To be deleted', $log->old_values['name']);
    }

    public function test_it_excludes_guarded_attributes_like_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret123')
        ]);

        $log = AuditLog::first();
        
        $this->assertArrayNotHasKey('password', $log->new_values);
        $this->assertArrayNotHasKey('remember_token', $log->new_values);
    }
}
