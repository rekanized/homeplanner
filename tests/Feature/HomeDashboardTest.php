<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ShoppingList;
use App\Models\ShoppingItem;
use App\Models\Todo;
use App\Models\TodoItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomeDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_correct_statistics()
    {
        $user = User::factory()->create();

        // Create a shopping list and items
        $shoppingList = ShoppingList::factory()->create();
        ShoppingItem::factory()->count(3)->create([
            'shopping_list_id' => $shoppingList->id,
            'is_checked' => false
        ]);
        ShoppingItem::factory()->count(2)->create([
            'shopping_list_id' => $shoppingList->id,
            'is_checked' => true
        ]);

        // Create a todo list and items
        $todo = Todo::factory()->create();
        TodoItem::factory()->count(4)->create([
            'todo_id' => $todo->id,
            'is_done' => false,
            'due_date' => now()->addDays(2),
        ]);
        TodoItem::factory()->count(1)->create([
            'todo_id' => $todo->id,
            'is_done' => false,
            'due_date' => now()->subDay(),
        ]);
        TodoItem::factory()->count(5)->create([
            'todo_id' => $todo->id,
            'is_done' => true,
            'completed_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Home\Dashboard::class)
            ->assertStatus(200)
            ->assertViewHas('shoppingItemsCount', 3)
            ->assertViewHas('todoItemsWaiting', 5)
            ->assertViewHas('todoItemsOverdue', 1)
            ->assertViewHas('totalCompleted', 5)
            ->assertViewHas('chartPoints')
            ->assertSee('3 <span style="font-size: 0.5em; opacity: 1;">Items</span>', false)
            ->assertSee('5 <span style="font-size: 0.5em; opacity: 1;">Tasks</span>', false)
            ->assertSee('1 overdue', false)
            ->assertSee('Task Productivity')
            ->assertDontSee('Economy Overview');
    }
}
