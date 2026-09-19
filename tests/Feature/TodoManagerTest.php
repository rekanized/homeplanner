<?php

namespace Tests\Feature;

use App\Livewire\Todo\TodoManager;
use App\Models\Todo;
use App\Models\TodoItem;
use App\Models\TodoTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TodoManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_admin' => true]);
    }

    public function test_todo_manager_is_accessible(): void
    {
        $this->actingAs($this->user)->get(route('todo.index'))->assertOk();
    }

    public function test_can_add_item_to_the_shared_todo_collection(): void
    {
        Livewire::actingAs($this->user)->test(TodoManager::class)->call('addItem', 'Shared task');

        $this->assertDatabaseHas('todo_items', [
            'todo_id' => Todo::sole()->id,
            'name' => 'Shared task',
        ]);
        $this->assertDatabaseCount('todos', 1);
    }

    public function test_can_toggle_and_delete_shared_todo_items(): void
    {
        $item = TodoItem::factory()->create(['todo_id' => Todo::sole()->id, 'is_done' => false, 'completed_at' => null]);
        $component = Livewire::actingAs($this->user)->test(TodoManager::class);

        $component->call('toggleItem', $item->id);
        $this->assertTrue($item->refresh()->is_done);
        $this->assertNotNull($item->completed_at);

        $component->call('toggleItem', $item->id)->call('deleteItem', $item->id);
        $this->assertDatabaseMissing('todo_items', ['id' => $item->id]);
    }

    public function test_can_reorder_items_in_the_shared_collection(): void
    {
        $todo = Todo::sole();
        $first = TodoItem::factory()->create(['todo_id' => $todo->id, 'sort_order' => 1]);
        $second = TodoItem::factory()->create(['todo_id' => $todo->id, 'sort_order' => 2]);

        Livewire::actingAs($this->user)->test(TodoManager::class)
            ->call('handleReorder', [$second->id, $first->id]);

        $this->assertSame(0, $second->refresh()->sort_order);
        $this->assertSame(1, $first->refresh()->sort_order);
    }

    public function test_tasks_support_multiple_case_insensitive_tags_and_remove_unused_tags(): void
    {
        $item = TodoItem::factory()->create(['todo_id' => Todo::sole()->id]);
        $component = Livewire::actingAs($this->user)->test(TodoManager::class);

        $component->call('attachTag', $item->id, 'Home')
            ->call('attachTag', $item->id, 'Urgent')
            ->call('attachTag', $item->id, 'home');

        $this->assertSame(['Home', 'Urgent'], $item->refresh()->tags()->orderBy('name')->pluck('name')->all());
        $this->assertDatabaseCount('todo_tags', 2);

        $home = TodoTag::where('name', 'Home')->sole();
        $component->set('selectedTags', [$home->id])->call('removeTag', $item->id, $home->id)->assertSet('selectedTags', []);
        $this->assertDatabaseMissing('todo_tags', ['id' => $home->id]);
    }

    public function test_global_tag_filter_matches_any_selected_tag(): void
    {
        $todo = Todo::sole();
        $workTask = TodoItem::factory()->create(['todo_id' => $todo->id, 'name' => 'Work task', 'is_done' => false]);
        $homeTask = TodoItem::factory()->create(['todo_id' => $todo->id, 'name' => 'Home task', 'is_done' => false]);
        $bothTask = TodoItem::factory()->create(['todo_id' => $todo->id, 'name' => 'Both task', 'is_done' => false]);
        $work = TodoTag::create(['name' => 'Work']);
        $home = TodoTag::create(['name' => 'Home']);
        $workTask->tags()->attach($work);
        $homeTask->tags()->attach($home);
        $bothTask->tags()->attach([$work->id, $home->id]);

        Livewire::actingAs($this->user)->test(TodoManager::class)
            ->set('selectedTags', [$work->id])
            ->assertSee('Work task')
            ->assertSee('Both task')
            ->assertDontSee('Home task')
            ->set('selectedTags', [$work->id, $home->id])
            ->assertSee('Work task')
            ->assertSee('Home task')
            ->assertSee('Both task')
            ->set('selectedTags', [])
            ->assertSee('Work task')
            ->assertSee('Home task');
    }

    public function test_the_todo_page_has_no_user_managed_lists(): void
    {
        Livewire::actingAs($this->user)->test(TodoManager::class)
            ->assertDontSee('My Lists')
            ->assertDontSee('Create New List');
    }

    public function test_adding_a_task_while_filtered_keeps_the_new_task_visible(): void
    {
        $tag = TodoTag::create(['name' => 'Home']);
        Livewire::actingAs($this->user)->test(TodoManager::class)
            ->set('selectedTags', [$tag->id])
            ->assertSee('No tasks match these tags.')
            ->call('addItem', 'New task to edit')
            ->assertSet('selectedTags', [])
            ->assertSee('New task to edit')
            ->assertDispatched('task-added');
    }
}
