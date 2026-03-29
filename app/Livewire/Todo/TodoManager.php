<?php

namespace App\Livewire\Todo;

use Livewire\Component;
use App\Models\Todo;
use App\Models\TodoItem;
use Livewire\Attributes\Computed;

class TodoManager extends Component
{
    public $activeTodoId;
    public $newListNames = [];
    public $selectedItems = [];
    public $selectedTags = [];

    protected $listeners = ['reorder' => 'handleReorder'];

    public function mount()
    {
        $firstTodo = Todo::orderBy('sort_order')->first();
        if ($firstTodo) {
            $this->activeTodoId = $firstTodo->id;
        }
    }

    #[Computed]
    public function todos()
    {
        return Todo::orderBy('sort_order')->get();
    }

    #[Computed]
    public function activeTodo()
    {
        return Todo::find($this->activeTodoId);
    }

    #[Computed]
    public function availableTags()
    {
        if (!$this->activeTodo) return collect();
        return collect($this->activeTodo->items()->whereNotNull('category')->where('category', '!=', '')->distinct()->pluck('category'));
    }

    #[Computed]
    public function items()
    {
        if (!$this->activeTodo) return collect();
        
        $query = $this->activeTodo->items()->orderBy('sort_order');
        
        if (!empty($this->selectedTags)) {
            $query->whereIn('category', $this->selectedTags);
        }
        
        return $query->get();
    }

    #[Computed]
    public function pendingItems()
    {
        return $this->items->where('is_done', false);
    }

    #[Computed]
    public function completedItems()
    {
        return $this->items->where('is_done', true);
    }

    #[Computed]
    public function groupedPendingItems()
    {
        $overdue = [];
        $today = [];
        $upcoming = [];
        $noDate = [];

        $now = now()->startOfDay();

        foreach ($this->pendingItems as $item) {
            if (!$item->due_date) {
                $noDate[] = $item;
            } else {
                $due = \Carbon\Carbon::parse($item->due_date)->startOfDay();
                if ($due->isBefore($now)) {
                    $overdue[] = $item;
                } elseif ($due->isSameDay($now)) {
                    $today[] = $item;
                } else {
                    $upcoming[] = $item;
                }
            }
        }

        return [
            'overdue' => collect($overdue),
            'today' => collect($today),
            'upcoming' => collect($upcoming),
            'no_date' => collect($noDate),
        ];
    }

    public function selectTodo($id)
    {
        $this->activeTodoId = $id;
        $this->selectedItems = [];
    }

    public function addTodo($name)
    {
        if (empty($name)) return;
        $todo = Todo::create([
            'name' => $name,
            'sort_order' => Todo::count()
        ]);
        $this->activeTodoId = $todo->id;
    }

    public function addList()
    {
        $todo = Todo::create([
            'name' => __('New List'),
            'sort_order' => Todo::count()
        ]);
        $this->activeTodoId = $todo->id;
    }

    public function updateListName($id, $newName)
    {
        $todo = Todo::find($id);
        if ($todo && !empty(trim($newName))) {
            $todo->update(['name' => $newName]);
        }
    }

    public function deleteTodo($id)
    {
        Todo::find($id)?->delete();
        if ($this->activeTodoId == $id) {
            $this->activeTodoId = Todo::orderBy('sort_order')->first()?->id;
        }
    }

    public function addItem($name = '')
    {
        if (!$this->activeTodoId) return;
        
        TodoItem::create([
            'todo_id' => $this->activeTodoId,
            'name' => $name,
            'sort_order' => TodoItem::where('todo_id', $this->activeTodoId)->count()
        ]);
    }

    public function updateItemName($id, $newName)
    {
        $item = TodoItem::find($id);
        if ($item && !empty(trim($newName))) {
            $item->update(['name' => $newName]);
        }
    }

    public function toggleItem($id)
    {
        $item = TodoItem::find($id);
        if ($item) {
            $item->update([
                'is_done' => !$item->is_done,
                'completed_at' => !$item->is_done ? now() : null
            ]);
        }
    }

    public function deleteItem($id)
    {
        TodoItem::find($id)?->delete();
    }

    public function handleReorder($itemIds)
    {
        foreach ($itemIds as $index => $id) {
            TodoItem::where('id', $id)->update(['sort_order' => $index]);
        }
    }

    public function updateItemCategory($id, $category)
    {
        $item = TodoItem::find($id);
        if ($item) {
            $item->update(['category' => $category]);
        }
    }

    public function updateItemDueDate($id, $date)
    {
        $item = TodoItem::find($id);
        if ($item) {
            $item->update(['due_date' => empty($date) ? null : \Carbon\Carbon::parse($date)->format('Y-m-d')]);
        }
    }

    public function moveItemToGroup($itemId, $targetGroup, $itemIdsInNewOrder)
    {
        $item = TodoItem::find($itemId);
        if ($item && $targetGroup) {
            $now = now()->startOfDay();
            if ($targetGroup === 'overdue' && (!$item->due_date || \Carbon\Carbon::parse($item->due_date)->isAfter($now->copy()->subDay()->endOfDay()))) {
                $item->due_date = $now->copy()->subDay()->format('Y-m-d');
            } elseif ($targetGroup === 'today') {
                $item->due_date = $now->format('Y-m-d');
            } elseif ($targetGroup === 'upcoming' && (!$item->due_date || \Carbon\Carbon::parse($item->due_date)->isBefore($now->copy()->addDay()->startOfDay()))) {
                $item->due_date = $now->copy()->addDay()->format('Y-m-d');
            } elseif ($targetGroup === 'no_date') {
                $item->due_date = null;
            }
            $item->save();
        }

        foreach ($itemIdsInNewOrder as $index => $id) {
            TodoItem::where('id', $id)->update(['sort_order' => $index]);
        }
    }

    public function handleListReorder($listIds)
    {
        foreach ($listIds as $index => $id) {
            Todo::where('id', $id)->update(['sort_order' => $index]);
        }
    }

    public function render()
    {
        return view('livewire.todo.todo-manager');
    }
}
