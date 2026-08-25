<?php

namespace Tests\Feature;

use App\Livewire\Home\Dashboard;
use App\Models\Chore;
use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use App\Models\Todo;
use App\Models\TodoItem;
use App\Models\User;
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
            'is_checked' => false,
        ]);
        ShoppingItem::factory()->count(2)->create([
            'shopping_list_id' => $shoppingList->id,
            'is_checked' => true,
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
        Chore::factory()->count(2)->create([
            'user_id' => $user->id,
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertStatus(200)
            ->assertViewHas('shoppingItemsCount', 3)
            ->assertViewHas('todoItemsWaiting', 5)
            ->assertViewHas('todoItemsOverdue', 1)
            ->assertViewHas('thisWeekCompleted', 7)
            ->assertViewHas('lastWeekCompleted', 0)
            ->assertViewHas('productivityDelta', 7)
            ->assertViewHas('completionRate', 58)
            ->assertViewHas('openWorkload', 5)
            ->assertViewHas('productivityWeeks', fn ($weeks) => $weeks->count() === 8
                && $weeks->last()['todo_count'] === 5
                && $weeks->last()['chore_count'] === 2
                && $weeks->last()['total'] === 7)
            ->assertSee('3 <span style="font-size: 0.5em; opacity: 1;">Items</span>', false)
            ->assertSee('5 <span style="font-size: 0.5em; opacity: 1;">Tasks</span>', false)
            ->assertSee('1 overdue', false)
            ->assertSee('Household productivity')
            ->assertDontSee('Economy Overview');
    }
}
