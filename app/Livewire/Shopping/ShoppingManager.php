<?php

namespace App\Livewire\Shopping;

use Livewire\Component;
use App\Models\ShoppingList;
use App\Models\ShoppingItem;
use App\Services\GrocerySortingService;
use Livewire\Attributes\Computed;

class ShoppingManager extends Component
{
    public $activeListId;
    public $newListNames = []; // For inline editing of list names

    public $isShopping = false;
    public $showFinishModal = false;

    protected $listeners = ['reorder' => 'handleReorder'];

    public function mount()
    {
        $firstList = ShoppingList::orderBy('sort_order')->first();
        if (!$firstList) {
            $firstList = ShoppingList::create(['name' => __('General Shopping')]);
        }
        $this->activeListId = $firstList->id;
    }

    #[Computed]
    public function lists()
    {
        return ShoppingList::orderBy('sort_order')->get();
    }

    #[Computed]
    public function activeList()
    {
        return ShoppingList::find($this->activeListId);
    }

    #[Computed]
    public function items()
    {
        return $this->activeList ? $this->activeList->items()->orderBy('sort_order')->get() : collect();
    }

    public function selectList($id)
    {
        $this->activeListId = $id;
    }

    public function addList()
    {
        $newList = ShoppingList::create([
            'name' => __('New List'),
            'sort_order' => ShoppingList::max('sort_order') + 1
        ]);
        $this->activeListId = $newList->id;
    }

    public function updateListName($id, $name)
    {
        $list = ShoppingList::find($id);
        if ($list && trim($name) !== '') {
            $list->update(['name' => trim($name)]);
        }
    }

    public function deleteList($id)
    {
        $list = ShoppingList::find($id);
        if ($list) {
            $list->delete();
            $this->mount(); // Reset to first available list
        }
    }

    public function addItem()
    {
        if (!$this->activeListId) return;

        ShoppingItem::create([
            'shopping_list_id' => $this->activeListId,
            'name' => '',
            'quantity' => 1,
            'sort_order' => ShoppingItem::where('shopping_list_id', $this->activeListId)->max('sort_order') + 1
        ]);
    }

    public function updateItem($id, $field, $value)
    {
        $item = ShoppingItem::find($id);
        if ($item) {
            $item->update([$field => $value]);
        }
    }

    public function incrementQuantity($id)
    {
        $item = ShoppingItem::find($id);
        if ($item) {
            $item->increment('quantity');
        }
    }

    public function decrementQuantity($id)
    {
        $item = ShoppingItem::find($id);
        if ($item && $item->quantity > 1) {
            $item->decrement('quantity');
        }
    }

    public function toggleCheck($id)
    {
        $item = ShoppingItem::find($id);
        if ($item) {
            $item->update(['is_checked' => !$item->is_checked]);
        }
    }

    public function deleteItem($id)
    {
        $item = ShoppingItem::find($id);
        if ($item) {
            $item->delete();
        }
    }



    public function handleReorder($type, $ids)
    {
        if ($type === 'items') {
            foreach ($ids as $index => $id) {
                ShoppingItem::where('id', $id)->update(['sort_order' => $index]);
            }
        } elseif ($type === 'lists') {
            foreach ($ids as $index => $id) {
                ShoppingList::where('id', $id)->update(['sort_order' => $index]);
            }
        }
    }

    public function startShopping(GrocerySortingService $sortingService)
    {
        $items = $this->items;
        if ($items->isEmpty()) return;

        // Sort items using the heuristic service
        $sortedItems = $items->sortBy(function($item) use ($sortingService) {
            return $sortingService->getSortScore($item->name);
        });

        // Batch update sort orders
        foreach ($sortedItems->values() as $index => $item) {
            $item->update(['sort_order' => $index]);
        }
        
        // Clear computed properties and refresh relationship
        unset($this->items);
        if ($this->activeList) {
            $this->activeList->load('items');
        }
    }

    public function enterShoppingMode()
    {
        $this->isShopping = true;
    }

    public function finishShopping(bool $clearAll = false)
    {
        if (!$this->activeList) return;

        if ($clearAll) {
            $this->activeList->items()->delete();
        } else {
            $this->activeList->items()->where('is_checked', true)->delete();
        }

        $this->isShopping = false;
    }

    public function render()
    {
        return view('livewire.shopping.shopping-manager');
    }
}
