<?php

namespace App\Livewire\Todo;

use App\Models\Todo;
use App\Models\TodoItem;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

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
        if (! $this->activeTodo) {
            return collect();
        }

        return collect($this->activeTodo->items()->whereNotNull('category')->where('category', '!=', '')->distinct()->pluck('category'));
    }

    #[Computed]
    public function items()
    {
        if (! $this->activeTodo) {
            return collect();
        }

        $query = $this->activeTodo->items()->orderBy('sort_order');

        if (! empty($this->selectedTags)) {
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
            if (! $item->due_date) {
                $noDate[] = $item;
            } else {
                $due = Carbon::parse($item->due_date)->startOfDay();
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
        $todo = Todo::find($id);
        if (! $todo) {
            return;
        }

        $this->activeTodoId = $todo->id;
        $this->selectedItems = [];
    }

    public function addTodo($name)
    {
        if (! is_string($name) || trim($name) === '' || mb_strlen(trim($name)) > 255) {
            return;
        }
        $todo = Todo::create([
            'name' => trim($name),
            'sort_order' => Todo::count(),
        ]);
        $this->activeTodoId = $todo->id;
    }

    public function addList()
    {
        $todo = Todo::create([
            'name' => __('New List'),
            'sort_order' => Todo::count(),
        ]);
        $this->activeTodoId = $todo->id;
    }

    public function updateListName($id, $newName)
    {
        $todo = Todo::find($id);
        if ($todo && is_string($newName) && trim($newName) !== '' && mb_strlen(trim($newName)) <= 255) {
            $todo->update(['name' => trim($newName)]);
        }
    }

    public function deleteTodo($id)
    {
        Todo::whereKey($id)->whereKey($this->activeTodoId)->first()?->delete();
        if ($this->activeTodoId == $id) {
            $this->activeTodoId = Todo::orderBy('sort_order')->first()?->id;
        }
    }

    public function addItem($name = '')
    {
        if (! Todo::whereKey($this->activeTodoId)->exists()) {
            return;
        }
        if (! is_string($name) || mb_strlen($name) > 255) {
            return;
        }

        $item = TodoItem::create([
            'todo_id' => $this->activeTodoId,
            'name' => $name,
            'sort_order' => TodoItem::where('todo_id', $this->activeTodoId)->count(),
        ]);

        $this->dispatch('task-added', itemId: $item->id);
    }

    public function updateItemName($id, $newName)
    {
        $item = $this->activeItem($id);
        if ($item && is_string($newName) && trim($newName) !== '' && mb_strlen(trim($newName)) <= 255) {
            $item->update(['name' => trim($newName)]);
        }
    }

    public function toggleItem($id)
    {
        $item = $this->activeItem($id);
        if ($item) {
            $item->update([
                'is_done' => ! $item->is_done,
                'completed_at' => ! $item->is_done ? now() : null,
            ]);
        }
    }

    public function deleteItem($id)
    {
        $this->activeItem($id)?->delete();
    }

    public function handleReorder($itemIds)
    {
        if (! is_array($itemIds)) {
            return;
        }

        foreach ($itemIds as $index => $id) {
            TodoItem::where('id', $id)
                ->where('todo_id', $this->activeTodoId)
                ->update(['sort_order' => $index]);
        }
    }

    public function updateItemCategory($id, $category)
    {
        $item = $this->activeItem($id);
        if ($item && is_string($category) && mb_strlen(trim($category)) <= 255) {
            $item->update(['category' => trim($category) ?: null]);
        }
    }

    public function updateItemDueDate($id, $date)
    {
        $item = $this->activeItem($id);
        if ($item) {
            $validated = validator(['date' => $date], ['date' => 'nullable|date_format:Y-m-d'])->validate();
            $item->update(['due_date' => $validated['date'] ?: null]);
        }
    }

    public function moveItemToGroup($itemId, $targetGroup, $itemIdsInNewOrder)
    {
        if (! in_array($targetGroup, ['overdue', 'today', 'upcoming', 'no_date'], true) || ! is_array($itemIdsInNewOrder)) {
            return;
        }

        $item = $this->activeItem($itemId);
        if ($item && $targetGroup) {
            $now = now()->startOfDay();
            if ($targetGroup === 'overdue' && (! $item->due_date || Carbon::parse($item->due_date)->isAfter($now->copy()->subDay()->endOfDay()))) {
                $item->due_date = $now->copy()->subDay()->format('Y-m-d');
            } elseif ($targetGroup === 'today') {
                $item->due_date = $now->format('Y-m-d');
            } elseif ($targetGroup === 'upcoming' && (! $item->due_date || Carbon::parse($item->due_date)->isBefore($now->copy()->addDay()->startOfDay()))) {
                $item->due_date = $now->copy()->addDay()->format('Y-m-d');
            } elseif ($targetGroup === 'no_date') {
                $item->due_date = null;
            }
            $item->save();
        }

        foreach ($itemIdsInNewOrder as $index => $id) {
            TodoItem::where('id', $id)
                ->where('todo_id', $this->activeTodoId)
                ->update(['sort_order' => $index]);
        }
    }

    public function handleListReorder($listIds)
    {
        if (! is_array($listIds)) {
            return;
        }

        foreach ($listIds as $index => $id) {
            Todo::where('id', $id)->update(['sort_order' => $index]);
        }
    }

    public function render()
    {
        return view('livewire.todo.todo-manager');
    }

    private function activeItem($id): ?TodoItem
    {
        return TodoItem::whereKey($id)
            ->where('todo_id', $this->activeTodoId)
            ->first();
    }
}
