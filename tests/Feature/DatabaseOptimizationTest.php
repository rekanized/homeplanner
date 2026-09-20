<?php

namespace Tests\Feature;

use App\Livewire\Admin\UserList;
use App\Livewire\Economy\EconomyManager;
use App\Livewire\Economy\MonthlyHistory;
use App\Livewire\Economy\SavingsHistory;
use App\Livewire\Home\Dashboard;
use App\Livewire\Kids\KidsManager;
use App\Models\Chore;
use App\Models\EconomySnapshot;
use App\Models\Expense;
use App\Models\Income;
use App\Models\PredefinedChore;
use App\Models\Redemption;
use App\Models\Saving;
use App\Models\SavingsSnapshot;
use App\Models\Setting;
use App\Models\Todo;
use App\Models\TodoItem;
use App\Models\User;
use App\Services\EconomySnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class DatabaseOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_economy_render_reads_each_collection_only_once(): void
    {
        $user = User::factory()->create();
        Income::factory()->create(['amount' => 1000]);
        Expense::factory()->create(['amount' => 100, 'payer_ids' => [$user->id]]);
        Saving::factory()->create(['amount' => 50]);

        $queries = $this->queries(fn () => Livewire::actingAs($user)->test(EconomyManager::class)->assertSee('850'));
        foreach (['incomes', 'expenses', 'savings', 'users'] as $table) {
            $this->assertCount(1, $this->readsFrom($queries, $table), "Repeated reads of {$table}");
        }
    }

    public function test_child_history_does_not_issue_user_queries_per_row(): void
    {
        $child = User::factory()->create(['is_child' => true]);
        Chore::factory()->create(['user_id' => $child->id, 'is_completed' => true, 'completed_at' => now()]);
        Redemption::create(['user_id' => $child->id, 'description' => 'First reward', 'score' => 1]);
        $initial = $this->queries(fn () => Livewire::actingAs($child)->test(KidsManager::class));

        Chore::factory()->count(9)->create(['user_id' => $child->id, 'is_completed' => true, 'completed_at' => now()]);
        for ($i = 0; $i < 9; $i++) {
            Redemption::create(['user_id' => $child->id, 'description' => "Reward {$i}", 'score' => 1]);
        }
        $expanded = $this->queries(fn () => Livewire::actingAs($child)->test(KidsManager::class));
        $this->assertCount(count($this->readsFrom($initial, 'users')), $this->readsFrom($expanded, 'users'));
    }

    public function test_disabled_dashboard_modules_do_not_query_their_tables(): void
    {
        $user = User::factory()->create();
        foreach (['economy', 'shopping', 'todo', 'kids'] as $module) {
            Setting::set("module_{$module}_enabled", false, 'modules');
        }
        $queries = $this->queries(fn () => Livewire::actingAs($user)->test(Dashboard::class)
            ->assertViewHas('completionRate', 0)->assertViewHas('openWorkload', 0));
        foreach (['incomes', 'expenses', 'savings_balances', 'shopping_items', 'todo_items', 'chores', 'predefined_chores'] as $table) {
            $this->assertCount(0, $this->readsFrom($queries, $table), "Disabled module queried {$table}");
        }
    }

    public function test_history_lists_load_summaries_and_only_the_selected_payload(): void
    {
        $user = User::factory()->create();
        foreach ([EconomySnapshot::class, SavingsSnapshot::class] as $model) {
            for ($i = 1; $i <= 3; $i++) {
                $model::create(array_merge(['month' => $i, 'year' => 2026, 'snapshot_data' => $model === EconomySnapshot::class ? ['incomes' => [], 'expenses' => [], 'savings' => [], 'categories' => []] : []],
                    $model === EconomySnapshot::class
                        ? ['total_income' => 100, 'total_expenses' => 20, 'total_savings' => 10]
                        : ['total_amount' => 100]));
            }
        }
        foreach ([MonthlyHistory::class => 'economy_snapshots', SavingsHistory::class => 'savings_snapshots'] as $component => $table) {
            $queries = $this->queries(fn () => Livewire::actingAs($user)->test($component));
            $payloadReads = array_filter($this->readsFrom($queries, $table), fn ($sql) => str_contains($sql, 'select *') || str_contains($sql, 'snapshot_data'));
            $this->assertCount(1, $payloadReads, 'Only the selected snapshot should load its payload');
            $this->assertStringContainsString('where', array_values($payloadReads)[0]);
        }
    }

    public function test_dashboard_date_boundaries_and_completion_counts(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 20)->setTime(12, 0));
        $user = User::factory()->create();
        $todo = Todo::sole();
        foreach ([now()->subDay(), now(), now()->addDay()] as $date) {
            TodoItem::factory()->create(['todo_id' => $todo->id, 'is_done' => false, 'due_date' => $date]);
        }
        foreach (['2026-09-13 23:59:59', '2026-09-14 00:00:00', '2026-09-20 23:59:59', '2026-09-21 00:00:00', '2026-07-26 23:59:59'] as $date) {
            TodoItem::factory()->create(['todo_id' => $todo->id, 'is_done' => true, 'completed_at' => $date, 'created_at' => '2026-01-01']);
        }
        Livewire::actingAs($user)->test(Dashboard::class)
            ->assertViewHas('todoItemsOverdue', 1)
            ->assertViewHas('thisWeekCompleted', 2)
            ->assertViewHas('lastWeekCompleted', 1)
            ->assertViewHas('completionRate', 0)
            ->assertViewHas('productivityWeeks', fn ($weeks) => $weeks->sum('total') === 3);
    }

    public function test_monthly_stats_include_month_start_and_exclude_next_month(): void
    {
        $this->travelTo(now()->setDate(2026, 12, 15));
        $child = User::factory()->create(['is_child' => true]);
        foreach (['2026-11-30 23:59:59', '2026-12-01 00:00:00', '2026-12-31 23:59:59', '2027-01-01 00:00:00'] as $date) {
            Chore::factory()->create(['user_id' => $child->id, 'is_completed' => true, 'completed_at' => $date, 'score' => 10]);
        }
        Chore::factory()->create(['user_id' => $child->id, 'is_completed' => false, 'completed_at' => '2026-12-15', 'score' => 100]);
        foreach ([$child, User::withMonthlyChoreStats()->findOrFail($child->id)] as $model) {
            $this->assertSame(20, $model->monthly_points);
            $this->assertSame(2, $model->monthly_finished_tasks);
        }
    }

    public function test_settings_batch_preserves_defaults_encryption_and_fresh_values(): void
    {
        Setting::set('module_todo_enabled', false);
        Setting::set('google_client_secret', 'test-secret');
        Setting::set('nullable', null);
        $defaults = ['module_todo_enabled' => true, 'google_client_secret' => null, 'missing' => 'fallback', 'nullable' => 'default'];
        $queries = $this->queries(function () use ($defaults) {
            $this->assertSame([
                'module_todo_enabled' => '0', 'google_client_secret' => 'test-secret', 'missing' => 'fallback', 'nullable' => 'default',
            ], Setting::getMany($defaults));
        });
        $this->assertCount(1, $this->readsFrom($queries, 'settings'));
        Setting::set('module_todo_enabled', true);
        $this->assertSame('1', Setting::getMany($defaults)['module_todo_enabled']);
    }

    public function test_snapshot_totals_reuse_captured_rows(): void
    {
        Income::factory()->create(['amount' => 100.25]);
        Income::factory()->create(['amount' => 50.10]);
        Expense::factory()->create(['amount' => 10.99]);
        Saving::factory()->create(['amount' => 20.01]);
        $queries = $this->queries(function () {
            $snapshot = app(EconomySnapshotService::class)->capture();
            $this->assertSame('150.35', $snapshot->total_income);
            $this->assertSame('10.99', $snapshot->total_expenses);
            $this->assertSame('20.01', $snapshot->total_savings);
        });
        foreach (['incomes', 'expenses', 'savings', 'savings_balances', 'expense_categories'] as $table) {
            $this->assertCount(1, $this->readsFrom($queries, $table));
        }
    }

    public function test_admin_master_checks_do_not_query_per_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $initial = $this->queries(fn () => Livewire::actingAs($admin)->test(UserList::class)
            ->assertViewHas('masterUserIds', [$admin->id])->assertViewHas('currentUserIsMaster', true));
        User::factory()->count(10)->create();
        $expanded = $this->queries(fn () => Livewire::actingAs($admin)->test(UserList::class));
        foreach (['users', 'settings'] as $table) {
            $this->assertCount(count($this->readsFrom($initial, $table)), $this->readsFrom($expanded, $table));
        }
        Setting::set('google_first_user_email', strtoupper($admin->email));
        Livewire::actingAs($admin)->test(UserList::class)->assertViewHas('currentUserIsMaster', true);
        Setting::set('google_first_user_email', 'absent@example.test');
        Livewire::actingAs($admin)->test(UserList::class)
            ->assertViewHas('masterUserIds', [])->assertViewHas('currentUserIsMaster', false);
    }

    public function test_economy_reordering_batches_reads_and_preserves_auditing(): void
    {
        $user = User::factory()->create();
        $expenses = Expense::factory()->count(8)->create(['sort_order' => 10]);
        $component = Livewire::actingAs($user)->test(EconomyManager::class);
        $queries = $this->queries(fn () => $component->call('reorder', 'expense', $expenses->modelKeys()));
        // One read for the update and one for the subsequent render, regardless of row count.
        $this->assertCount(2, $this->readsFrom($queries, 'expenses'));
        foreach ($expenses as $index => $expense) {
            $this->assertSame($index, $expense->fresh()->sort_order);
            $this->assertDatabaseHas('audit_logs', [
                'auditable_type' => Expense::class, 'auditable_id' => $expense->id, 'event' => 'updated',
            ]);
        }
        $queries = $this->queries(fn () => $component->call('reorder', 'expense', $expenses->modelKeys()));
        $this->assertCount(0, array_filter($queries, fn ($sql) => str_starts_with($sql, 'update "expenses"')));
    }

    public function test_recurring_generation_batches_reads_and_respects_day_boundaries(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 20)->setTime(12, 0));
        $children = User::factory()->count(4)->create(['is_child' => true]);
        $parent = User::factory()->create(['is_child' => false]);
        $template = PredefinedChore::create([
            'title' => 'Daily task', 'score' => 10, 'needs_approval' => true, 'recurrence_type' => 'daily',
            'assigned_user_ids' => [...$children->modelKeys(), $children[0]->id, $parent->id, 99999],
        ]);
        foreach (['2026-09-19 23:59:59', '2026-09-20 00:00:00', '2026-09-21 00:00:00'] as $i => $date) {
            Chore::factory()->create(['source_template_id' => $template->id, 'user_id' => $children[$i]->id, 'created_at' => $date]);
        }
        $queries = $this->queries(fn () => $this->artisan('kids:generate-recurring')->expectsOutputToContain('Generated 3')->assertSuccessful());
        $this->assertCount(1, $this->readsFrom($queries, 'users'));
        $this->assertCount(1, $this->readsFrom($queries, 'chores'));
        $this->assertDatabaseCount('chores', 6);
        $this->assertDatabaseMissing('chores', ['user_id' => $parent->id]);
        $this->assertSame(3, Chore::where('needs_approval', true)->count());
        $this->artisan('kids:generate-recurring')->expectsOutputToContain('Generated 0')->assertSuccessful();
        $this->assertDatabaseCount('chores', 6);
    }

    public function test_hot_queries_use_indexes_for_filters_and_ordering(): void
    {
        $queries = [
            ['select * from audit_logs order by created_at desc, id desc limit 20', 'audit_logs_created_id_index'],
            ['select * from shopping_items where shopping_list_id = 1 order by sort_order', 'shopping_items_list_sort_index'],
            ['select count(*) from shopping_items where is_checked = 0', 'shopping_items_checked_list_index'],
            ['select * from todo_items where todo_id = 1 order by sort_order', 'todo_items_list_sort_index'],
            ["select count(*) from todo_items where is_done = 0 and due_date < '2026-09-20'", 'todo_items_done_due_index'],
            ["select count(*) from todo_items where is_done = 1 and completed_at >= '2026-09-01' and completed_at < '2026-10-01'", 'todo_items_done_completed_index'],
            ["select count(*) from todo_items where created_at >= '2026-09-01'", 'todo_items_created_done_index'],
            ['select * from todo_item_tag where todo_tag_id = 1', 'todo_item_tag_tag_item_index'],
            ["select sum(score) from chores where user_id = 1 and is_completed = 1 and completed_at >= '2026-09-01' and completed_at < '2026-10-01'", 'chores_user_completed_date_index'],
            ['select * from chores where user_id = 1 and is_completed = 1 order by created_at desc limit 10', 'chores_user_completed_created_index'],
            ["select count(*) from chores where is_completed = 1 and completed_at >= '2026-09-01' and completed_at < '2026-10-01'", 'chores_completed_date_index'],
            ['select * from chores where is_completed = 1 order by created_at desc limit 20', 'chores_completed_created_index'],
            ["select count(*) from chores where created_at >= '2026-09-01'", 'chores_created_completed_index'],
            ["select user_id from chores where source_template_id = 1 and user_id in (1, 2) and created_at >= '2026-09-20' and created_at < '2026-09-21'", 'chores_template_user_created_index'],
            ['select * from chores where is_pending_approval = 1', 'chores_pending_approval_index'],
            ['select * from redemptions where user_id = 1 order by created_at desc limit 10', 'redemptions_user_created_index'],
            ['select * from redemptions order by created_at desc limit 20', 'redemptions_created_index'],
            ['select id from economy_snapshots order by year desc, month desc, id desc', 'economy_snapshots_year_month_id_index'],
            ['select id from savings_snapshots order by created_at desc, id desc', 'savings_snapshots_created_id_index'],
            ['select id from savings_snapshots where year = 2026 and month = 9', 'savings_snapshots_year_month_index'],
        ];
        foreach ($queries as [$sql, $index]) {
            $plan = implode(' ', array_column(DB::select('EXPLAIN QUERY PLAN '.$sql), 'detail'));
            $this->assertStringContainsString($index, $plan, $sql);
            $this->assertStringNotContainsString('USE TEMP B-TREE', $plan, $sql);
        }
    }

    public function test_index_migration_is_reversible_without_changing_household_data(): void
    {
        $item = TodoItem::factory()->create(['todo_id' => Todo::sole()->id]);
        $migration = require database_path('migrations/2026_09_20_161100_add_query_indexes.php');
        $before = $item->fresh()->getRawOriginal();
        $migration->down();
        $this->assertFalse(Schema::hasIndex('todo_items', 'todo_items_done_due_index'));
        $migration->up();
        $this->assertTrue(Schema::hasIndex('todo_items', 'todo_items_done_due_index'));
        $this->assertSame($before, $item->fresh()->getRawOriginal());
    }

    private function queries(callable $callback): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        try {
            $callback();

            return array_column(DB::getQueryLog(), 'query');
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }
    }

    private function readsFrom(array $queries, string $table): array
    {
        return array_values(array_filter($queries, fn ($sql) => str_starts_with($sql, 'select') && str_contains($sql, 'from "'.$table.'"')));
    }
}
