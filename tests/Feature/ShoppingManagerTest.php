<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ShoppingList;
use App\Models\ShoppingItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShoppingManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_admin' => true]);
    }

    public function test_shopping_manager_is_accessible()
    {
        $this->actingAs($this->user)
            ->get(route('shopping.index'))
            ->assertStatus(200);
    }

    public function test_can_create_shopping_list()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Shopping\ShoppingManager::class)
            ->call('addList');

        $this->assertEquals(2, ShoppingList::count()); // 1 created in mount() + 1 here
    }

    public function test_can_add_item_to_list()
    {
        $list = ShoppingList::factory()->create();

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Shopping\ShoppingManager::class)
            ->set('activeListId', $list->id)
            ->call('addItem');

        $this->assertEquals(1, ShoppingItem::where('shopping_list_id', $list->id)->count());
    }

    public function test_can_toggle_item_check()
    {
        $item = ShoppingItem::factory()->create(['is_checked' => false]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Shopping\ShoppingManager::class)
            ->call('toggleCheck', $item->id);

        $this->assertTrue($item->refresh()->is_checked);
    }

    public function test_can_bulk_delete_items()
    {
        $list = ShoppingList::factory()->create();
        $items = ShoppingItem::factory()->count(3)->create(['shopping_list_id' => $list->id]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Shopping\ShoppingManager::class)
            ->set('selectedItems', $items->pluck('id')->toArray())
            ->call('bulkDelete');

        $this->assertEquals(0, ShoppingItem::count());
    }

    public function test_can_reorder_items()
    {
        $list = ShoppingList::factory()->create();
        $item1 = ShoppingItem::factory()->create(['shopping_list_id' => $list->id, 'sort_order' => 1]);
        $item2 = ShoppingItem::factory()->create(['shopping_list_id' => $list->id, 'sort_order' => 2]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Shopping\ShoppingManager::class)
            ->call('handleReorder', 'items', [$item2->id, $item1->id]);

        $this->assertEquals(0, $item2->refresh()->sort_order);
        $this->assertEquals(1, $item1->refresh()->sort_order);
    }
}
