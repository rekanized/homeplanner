<?php

namespace App\Livewire\Todo;

use App\Models\Todo;
use App\Models\TodoItem;
use App\Models\TodoTag;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TodoManager extends Component
{
    public array $selectedTags = [];

    protected $listeners = ['reorder' => 'handleReorder'];

    #[Computed]
    public function sharedTodo(): Todo
    {
        return Todo::query()->orderBy('id')->firstOrCreate([
            'name' => 'Tasks',
        ], [
            'color' => '#4f46e5',
            'sort_order' => 0,
        ]);
    }

    #[Computed]
    public function availableTags()
    {
        return TodoTag::query()->has('items')->orderBy('name')->get();
    }

    #[Computed]
    public function items()
    {
        $query = $this->sharedTodo->items()->with('tags')->orderBy('sort_order');

        if ($this->selectedTags !== []) {
            $query->whereHas('tags', fn ($tagQuery) => $tagQuery->whereIn('todo_tags.id', $this->selectedTags));
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

                continue;
            }

            $due = Carbon::parse($item->due_date)->startOfDay();
            if ($due->isBefore($now)) {
                $overdue[] = $item;
            } elseif ($due->isSameDay($now)) {
                $today[] = $item;
            } else {
                $upcoming[] = $item;
            }
        }

        return [
            'overdue' => collect($overdue),
            'today' => collect($today),
            'upcoming' => collect($upcoming),
            'no_date' => collect($noDate),
        ];
    }

    public function updatedSelectedTags(): void
    {
        $tagIds = collect($this->selectedTags)
            ->filter(fn ($id) => filter_var($id, FILTER_VALIDATE_INT) !== false)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $this->selectedTags = TodoTag::whereIn('id', $tagIds)
            ->pluck('id')
            ->all();
    }

    public function addItem(string $name = ''): void
    {
        if (mb_strlen($name) > 255) {
            return;
        }

        $todo = $this->sharedTodo;
        $item = TodoItem::create([
            'todo_id' => $todo->id,
            'name' => $name,
            'sort_order' => $todo->items()->count(),
        ]);

        // A newly added, untagged task must remain visible and editable.
        $this->selectedTags = [];
        $this->dispatch('task-added', itemId: $item->id);
    }

    public function updateItemName($id, $newName): void
    {
        $item = $this->activeItem($id);
        if ($item && is_string($newName) && trim($newName) !== '' && mb_strlen(trim($newName)) <= 255) {
            $item->update(['name' => trim($newName)]);
        }
    }

    public function toggleItem($id): void
    {
        $item = $this->activeItem($id);
        if ($item) {
            $item->update([
                'is_done' => ! $item->is_done,
                'completed_at' => ! $item->is_done ? now() : null,
            ]);
        }
    }

    public function deleteItem($id): void
    {
        $item = $this->activeItem($id);
        if (! $item) {
            return;
        }

        $tagIds = $item->tags()->pluck('todo_tags.id');
        $item->delete();
        $this->removeUnusedTags($tagIds);
    }

    public function attachTag($id, $name): void
    {
        $item = $this->activeItem($id);
        $name = is_string($name) ? trim($name) : '';
        if (! $item || $name === '' || mb_strlen($name) > 255) {
            return;
        }

        $normalized = mb_strtolower($name);
        $tag = TodoTag::where('normalized_name', $normalized)->first();
        $tag ??= TodoTag::create(['name' => $name]);

        $item->tags()->syncWithoutDetaching([$tag->id]);
    }

    public function removeTag($id, $tagId): void
    {
        $item = $this->activeItem($id);
        if (! $item || ! filter_var($tagId, FILTER_VALIDATE_INT)) {
            return;
        }

        $item->tags()->detach($tagId);
        $this->removeUnusedTags(collect([(int) $tagId]));
    }

    public function handleReorder($itemIds): void
    {
        if (! is_array($itemIds)) {
            return;
        }

        DB::transaction(function () use ($itemIds) {
            foreach ($itemIds as $index => $id) {
                TodoItem::where('id', $id)
                    ->where('todo_id', $this->sharedTodo->id)
                    ->update(['sort_order' => $index]);
            }
        });
    }

    public function updateItemDueDate($id, $date): void
    {
        $item = $this->activeItem($id);
        if ($item) {
            $validated = validator(['date' => $date], ['date' => 'nullable|date_format:Y-m-d'])->validate();
            $item->update(['due_date' => $validated['date'] ?: null]);
        }
    }

    public function moveItemToGroup($itemId, $targetGroup, $itemIdsInNewOrder): void
    {
        if (! in_array($targetGroup, ['overdue', 'today', 'upcoming', 'no_date'], true) || ! is_array($itemIdsInNewOrder)) {
            return;
        }

        $item = $this->activeItem($itemId);
        if ($item) {
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

        $this->handleReorder($itemIdsInNewOrder);
    }

    public function render()
    {
        return view('livewire.todo.todo-manager');
    }

    private function activeItem($id): ?TodoItem
    {
        return TodoItem::whereKey($id)
            ->where('todo_id', $this->sharedTodo->id)
            ->first();
    }

    private function removeUnusedTags($tagIds): void
    {
        $unusedTagIds = TodoTag::whereIn('id', $tagIds)
            ->doesntHave('items')
            ->pluck('id');

        TodoTag::whereKey($unusedTagIds)->delete();
        $this->selectedTags = array_values(array_diff($this->selectedTags, $unusedTagIds->all()));
    }
}
