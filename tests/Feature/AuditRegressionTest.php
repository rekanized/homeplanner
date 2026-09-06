<?php

namespace Tests\Feature;

use App\Livewire\Admin\UserList;
use App\Livewire\Auth\Login;
use App\Livewire\Economy\EconomyManager;
use App\Livewire\Kids\KidsManager;
use App\Livewire\Sidebar;
use App\Livewire\Todo\TodoManager;
use App\Models\Chore;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PredefinedChore;
use App\Models\Setting;
use App\Models\Todo;
use App\Models\TodoItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class AuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_email_is_validated_after_normalization(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@example.test']);
        Livewire::actingAs($admin)->test(UserList::class)
            ->call('openCreateModal')
            ->set('name', 'Duplicate Admin')
            ->set('email', 'ADMIN@example.test')
            ->set('password', 'password123')
            ->call('createUser')
            ->assertHasErrors(['email' => 'unique'])
            ->call('openCreateModal')
            ->assertHasNoErrors();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_accepts_normalized_email(): void
    {
        $user = User::factory()->create(['email' => 'member@example.test', 'password' => 'password123']);
        Livewire::test(Login::class)
            ->set('email', 'MEMBER@example.test')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_switching_and_creating_lists_clear_previous_tag_filter(): void
    {
        $user = User::factory()->create();
        $first = Todo::factory()->create();
        $second = Todo::factory()->create();
        TodoItem::factory()->create(['todo_id' => $second->id, 'name' => 'Visible second task', 'category' => null, 'is_done' => false]);
        Livewire::actingAs($user)->test(TodoManager::class)
            ->set('selectedTags', ['first-only'])
            ->call('selectTodo', $second->id)
            ->assertSet('selectedTags', [])
            ->assertSee('Visible second task')
            ->set('selectedTags', ['first-only'])
            ->call('addList')
            ->assertSet('selectedTags', [])
            ->set('selectedTags', ['first-only'])
            ->call('addTodo', 'Another list')
            ->assertSet('selectedTags', []);
    }

    public function test_category_rename_rejects_duplicates_and_preserves_expense_totals(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $first = ExpenseCategory::create(['name' => 'Food']);
        $second = ExpenseCategory::create(['name' => 'Travel']);
        $expense = Expense::factory()->create(['category' => 'Food', 'amount' => 125]);
        Livewire::actingAs($admin)->test(EconomyManager::class)
            ->call('updateExpenseCategory', $first->id, 'name', 'Travel')
            ->assertHasErrors(['categoryNames.'.$first->id])
            ->call('updateExpenseCategory', $first->id, 'name', 'Groceries')
            ->assertHasNoErrors();
        $this->assertSame('Groceries', $first->fresh()->name);
        $this->assertSame('Groceries', $expense->fresh()->category);
        $this->assertEquals(125, $expense->fresh()->amount);
    }

    public function test_role_buttons_set_the_requested_role_when_clicked_repeatedly(): void
    {
        $master = User::factory()->create(['is_admin' => true]);
        $member = User::factory()->create(['is_child' => false]);
        $component = Livewire::actingAs($master)->test(UserList::class);
        $component->call('setChildStatus', $member->id, false);
        $this->assertFalse($member->fresh()->is_child);
        $component->call('setChildStatus', $member->id, true)->call('setChildStatus', $member->id, true);
        $this->assertTrue($member->fresh()->is_child);
        $component->call('setChildStatus', $master->id, true);
        $this->assertFalse($master->fresh()->is_child);
    }

    public function test_recurrence_switching_and_validation_use_the_correct_day_type(): void
    {
        $parent = User::factory()->create(['is_admin' => true]);
        $child = User::factory()->create(['is_child' => true]);
        $component = Livewire::actingAs($parent)->test(KidsManager::class)
            ->call('openManageTemplatesModal')
            ->set('templateTitle', 'Recurring task')
            ->set('templateAssignedUserIds', [$child->id])
            ->set('templateRecurrenceType', 'monthly')
            ->set('templateRecurrenceDay', 15)
            ->set('templateRecurrenceType', 'weekly')
            ->assertSet('templateRecurrenceDay', [])
            ->call('saveTemplate')
            ->assertHasErrors(['templateRecurrenceDay'])
            ->set('templateRecurrenceDay', ['Monday'])
            ->call('saveTemplate')
            ->assertHasNoErrors();
        $template = PredefinedChore::first();
        $this->assertSame(['Monday'], $template->recurrence_day);
        $component->call('editTemplate', $template->id)
            ->set('templateRecurrenceType', 'monthly')
            ->set('templateRecurrenceDay', 32)
            ->call('saveTemplate')
            ->assertHasErrors(['templateRecurrenceDay'])
            ->set('templateRecurrenceDay', 15)
            ->call('saveTemplate')
            ->assertHasNoErrors()
            ->call('editTemplate', $template->id)
            ->assertSet('templateRecurrenceDay', 15)
            ->call('openManageTemplatesModal')
            ->assertSet('templateRecurrenceType', 'none')
            ->assertSet('templateAssignedUserIds', []);
    }

    public function test_invalid_point_adjustments_display_feedback(): void
    {
        $parent = User::factory()->create(['is_admin' => true]);
        $child = User::factory()->create(['is_child' => true]);
        Livewire::actingAs($parent)->test(KidsManager::class)
            ->call('openAdjustPointsModal', $child->id)
            ->call('adjustPoints')
            ->assertHasErrors(['adjustAmount'])
            ->assertSee('The adjust amount field must be at least 1.');
    }

    public function test_sidebar_refreshes_modules_and_hides_admin_links_from_members(): void
    {
        $member = User::factory()->create(['is_admin' => false]);
        $component = Livewire::actingAs($member)->test(Sidebar::class)
            ->assertDontSee('User list');
        Setting::set('module_shopping_enabled', false, 'modules');
        $component->dispatch('modules-updated')->assertViewHas('shoppingEnabled', false);
    }

    public function test_admin_and_kids_layouts_load_accessibility_script_on_direct_navigation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        foreach (['/kids', '/admin/users', '/admin/logs', '/admin/settings'] as $path) {
            $this->actingAs($admin)->get($path)->assertOk()->assertSee('/js/app.js')->assertSee('id="main-content"', false);
        }
    }

    public function test_invalid_proof_upload_is_rejected_before_preview_rendering(): void
    {
        $parent = User::factory()->create(['is_admin' => true]);
        $child = User::factory()->create(['is_child' => true]);
        $chore = Chore::factory()->create(['user_id' => $child->id, 'needs_approval' => true, 'is_completed' => false]);
        Livewire::actingAs($parent)->test(KidsManager::class)
            ->call('completeChore', $chore->id)
            ->set('proofImage', UploadedFile::fake()->create('proof.txt', 10, 'text/plain'))
            ->assertHasErrors(['proofImage'])
            ->assertSet('proofImage', null)
            ->assertSee('The proof image field must be an image.')
            ->set('proofImage', UploadedFile::fake()->image('proof.png'))
            ->assertHasNoErrors();
    }
}
