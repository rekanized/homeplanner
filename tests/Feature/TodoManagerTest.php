<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Todo;
use App\Models\TodoItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TodoManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_admin' => true]);
    }

    public function test_todo_manager_is_accessible()
    {
        $this->actingAs($this->user)
            ->get(route('todo.index'))
            ->assertStatus(200);
    }

    public function test_can_create_todo_list()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Todo\TodoManager::class)
            ->call('addList');

        $this->assertEquals(1, Todo::count());
    }

    public function test_can_add_item_to_todo_list()
    {
        $todo = Todo::factory()->create();

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Todo\TodoManager::class)
            ->set('activeTodoId', $todo->id)
            ->call('addItem');

        $this->assertEquals(1, TodoItem::where('todo_id', $todo->id)->count());
    }

    public function test_can_toggle_todo_item_and_set_completed_at()
    {
        $item = TodoItem::factory()->create(['is_done' => false, 'completed_at' => null]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Todo\TodoManager::class)
            ->call('toggleItem', $item->id);

        $this->assertTrue($item->refresh()->is_done);
        $this->assertNotNull($item->completed_at);
        
        // toggle back
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Todo\TodoManager::class)
            ->call('toggleItem', $item->id);

        $this->assertFalse($item->refresh()->is_done);
        $this->assertNull($item->completed_at);
    }

    public function test_can_delete_todo_item()
    {
        $todo = Todo::factory()->create();
        $item = TodoItem::factory()->create(['todo_id' => $todo->id]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Todo\TodoManager::class)
            ->call('deleteItem', $item->id);

        $this->assertEquals(0, TodoItem::count());
    }

    public function test_can_reorder_todo_items()
    {
        $todo = Todo::factory()->create();
        $item1 = TodoItem::factory()->create(['todo_id' => $todo->id, 'sort_order' => 1]);
        $item2 = TodoItem::factory()->create(['todo_id' => $todo->id, 'sort_order' => 2]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Todo\TodoManager::class)
            ->call('handleReorder', [$item2->id, $item1->id]);

        $this->assertEquals(0, $item2->refresh()->sort_order);
        $this->assertEquals(1, $item1->refresh()->sort_order);
    }
}
