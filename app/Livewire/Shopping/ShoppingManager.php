<?php

namespace App\Livewire\Shopping;

use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use App\Services\GrocerySortingService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShoppingManager extends Component
{
    public $activeListId;

    public $newListNames = []; // For inline editing of list names

    protected $listeners = ['reorder' => 'handleReorder'];

    public function mount()
    {
        $firstList = ShoppingList::orderBy('sort_order')->first();
        if (! $firstList) {
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
        $list = ShoppingList::find($id);
        if ($list) {
            $this->activeListId = $list->id;
        }
    }

    public function addList()
    {
        $newList = ShoppingList::create([
            'name' => __('New List'),
            'sort_order' => ShoppingList::max('sort_order') + 1,
        ]);
        $this->activeListId = $newList->id;
    }

    public function updateListName($id, $name)
    {
        $list = ShoppingList::find($id);
        if ($list && is_string($name) && trim($name) !== '' && mb_strlen(trim($name)) <= 255) {
            $list->update(['name' => trim($name)]);
        }
    }

    public function deleteList($id)
    {
        $list = ShoppingList::whereKey($id)->whereKey($this->activeListId)->first();
        if ($list) {
            $list->delete();
            $this->mount(); // Reset to first available list
        }
    }

    public function addItem()
    {
        if (! ShoppingList::whereKey($this->activeListId)->exists()) {
            return;
        }

        $item = ShoppingItem::create([
            'shopping_list_id' => $this->activeListId,
            'name' => '',
            'quantity' => 1,
            'sort_order' => ShoppingItem::where('shopping_list_id', $this->activeListId)->max('sort_order') + 1,
        ]);

        $this->dispatch('shopping-item-added', itemId: $item->id);
    }

    public function updateItem($id, $field, $value)
    {
        if ($field !== 'name' || ! is_string($value) || mb_strlen($value) > 255) {
            return;
        }

        $this->activeItem($id)?->update(['name' => trim($value)]);
    }

    public function incrementQuantity($id)
    {
        $item = $this->activeItem($id);
        if ($item && $item->quantity < 999) {
            $item->increment('quantity');
        }
    }

    public function decrementQuantity($id)
    {
        $item = $this->activeItem($id);
        if ($item && $item->quantity > 1) {
            $item->decrement('quantity');
        }
    }

    public function toggleCheck($id)
    {
        $item = $this->activeItem($id);
        if ($item) {
            $item->update(['is_checked' => ! $item->is_checked]);
        }
    }

    public function deleteItem($id)
    {
        $item = $this->activeItem($id);
        if ($item) {
            $item->delete();
        }
    }

    public function clearCheckedItems()
    {
        if (! ShoppingList::whereKey($this->activeListId)->exists()) {
            return;
        }

        $deletedCount = ShoppingItem::where('shopping_list_id', $this->activeListId)
            ->where('is_checked', true)
            ->delete();

        if ($deletedCount > 0) {
            session()->flash('message', __('Checked items removed.'));
        }
    }

    public function handleReorder($type, $ids)
    {
        if (! is_array($ids)) {
            return;
        }
        $ids = array_values(array_unique(array_filter($ids, 'is_numeric')));

        if ($type === 'items') {
            foreach ($ids as $index => $id) {
                ShoppingItem::where('id', $id)
                    ->where('shopping_list_id', $this->activeListId)
                    ->update(['sort_order' => $index]);
            }
        } elseif ($type === 'lists') {
            foreach ($ids as $index => $id) {
                ShoppingList::where('id', $id)->update(['sort_order' => $index]);
            }
        }
    }

    public function sortItems(GrocerySortingService $sortingService)
    {
        $items = $this->items;
        if ($items->isEmpty()) {
            return;
        }

        // Sort items using the heuristic service
        $sortedItems = $items->sortBy(function ($item) use ($sortingService) {
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

        session()->flash('message', __('Shopping list sorted!'));
    }

    public function render()
    {
        return view('livewire.shopping.shopping-manager');
    }

    private function activeItem($id): ?ShoppingItem
    {
        return ShoppingItem::whereKey($id)
            ->where('shopping_list_id', $this->activeListId)
            ->first();
    }
}
