<?php

namespace Tests\Feature;

use App\Livewire\Admin\AuditLogList;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuditLogPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_per_page_selector_changes_page_size_and_resets_to_first_page()
    {
        $user = User::factory()->create(['is_admin' => true]);

        for ($index = 1; $index <= 30; $index++) {
            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'updated',
                'auditable_type' => User::class,
                'auditable_id' => $index,
                'old_values' => ['name' => 'Old '.$index],
                'new_values' => ['name' => 'New '.$index],
                'created_at' => now()->addSeconds($index),
                'updated_at' => now()->addSeconds($index),
            ]);
        }

        $totalLogs = AuditLog::count();

        Livewire::actingAs($user)
            ->test(AuditLogList::class)
            ->assertViewHas('logs', fn ($logs) => $logs->perPage() === 25 && $logs->total() === $totalLogs && $logs->count() === 25)
            ->set('perPage', 10)
            ->assertViewHas('logs', fn ($logs) => $logs->perPage() === 10 && $logs->total() === $totalLogs && $logs->count() === 10);
    }
}