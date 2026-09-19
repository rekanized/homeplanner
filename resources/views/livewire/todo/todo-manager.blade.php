<div class="animate-in todo-manager" x-data @task-added.window="$nextTick(() => {
    const input = document.getElementById('task-input-' + $event.detail.itemId);
    if (input) {
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => input.focus(), 100);
    }
})">
    <div class="flex-header">
        <div>
            <h2 class="responsive-title">{{ __('Todo') }}</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-top: var(--space-1);">{{ __('Organize your tasks and stay productive') }}</p>
        </div>
        <button wire:click="addItem('')" class="btn btn-primary desktop-only" style="display: flex; align-items: center; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            {{ __('Add Task') }}
        </button>
    </div>

    <div class="flex-cards list-toolbar" style="align-items: center; justify-content: flex-end; margin-bottom: var(--space-8);">
        @if($this->availableTags->isNotEmpty())
            <div class="todo-filter-control" style="position: relative;" x-data="{ open: false }">
                <button @click="open = !open" :aria-expanded="open" class="btn todo-filter-trigger" style="background: var(--bg-card); padding: 8px 16px; border-radius: 12px; font-size: 12px; font-weight: 800; color: var(--text-muted); display: flex; align-items: center; gap: 8px; border: 1px solid var(--border-color); height: 44px; cursor: pointer;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    {{ __('Filter') }}
                    @if(count($selectedTags) > 0)
                        <span class="badge" style="background: var(--primary); color: white; padding: 2px 6px; font-size: 10px;">{{ count($selectedTags) }}</span>
                    @endif
                </button>

                <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false" class="todo-filter-popout" style="top: calc(100% + 8px); background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 12px; min-width: 200px; box-shadow: var(--shadow-lg);" x-transition x-cloak>
                    <div style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--border-color);">{{ __('Filter by Tag') }}</div>
                    <div style="display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto;">
                        @foreach($this->availableTags as $tag)
                            <label style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; color: var(--text-color); cursor: pointer;">
                                <input type="checkbox" wire:model.live="selectedTags" value="{{ $tag->id }}" style="width: 18px; height: 18px; accent-color: var(--primary);">
                                <span>{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if(count($selectedTags) > 0)
                        <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                            <button wire:click="$set('selectedTags', [])" style="width: 100%; padding: 8px; border: none; background: transparent; color: var(--danger); font-size: 12px; font-weight: 800; cursor: pointer;">{{ __('Clear Filters') }}</button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div style="display: flex; flex-direction: column; gap: 8px;">
        @if($this->pendingItems->isEmpty())
            <div class="card" style="border-radius: 28px; overflow: hidden; margin-bottom: 24px;">
                <div style="padding: 40px; text-align: center; color: var(--text-muted); font-size: 14px; font-weight: 500;">
                    {{ count($selectedTags) && $this->items->isEmpty() ? __('No tasks match these tags.') : ($this->items->isEmpty() ? __('No tasks yet. Add one to get started.') : __('No pending tasks.')) }}
                    @if(count($selectedTags))
                        <button type="button" class="btn todo-clear-filters" wire:click="$set('selectedTags', [])">{{ __('Clear Filters') }}</button>
                    @endif
                </div>
            </div>
        @else
            @foreach(['overdue' => __('Overdue'), 'today' => __('Due Today'), 'upcoming' => __('Upcoming'), 'no_date' => __('No Due Date')] as $groupKey => $groupLabel)
                @if($this->groupedPendingItems[$groupKey]->isNotEmpty() || ($groupKey === 'no_date' && $this->pendingItems->isNotEmpty()))
                    @if($this->groupedPendingItems['overdue']->isNotEmpty() || $this->groupedPendingItems['today']->isNotEmpty() || $this->groupedPendingItems['upcoming']->isNotEmpty())
                        @if($groupKey !== 'no_date' || $this->groupedPendingItems['no_date']->isNotEmpty())
                            <div style="padding: 16px 16px 8px; display: flex; align-items: center; gap: 8px;">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: {{ $groupKey === 'overdue' ? 'var(--danger)' : ($groupKey === 'today' ? 'var(--warning)' : ($groupKey === 'upcoming' ? 'var(--primary)' : 'var(--text-muted)')) }};"></div>
                                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">
                                    {{ $groupLabel }} <span style="opacity: 0.5; margin-left: 4px;">{{ $this->groupedPendingItems[$groupKey]->count() }}</span>
                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="card" style="border-radius: 28px; overflow: hidden; margin-bottom: 8px;">

                        <div class="eco-grid-body" data-group="{{ $groupKey }}"
                            x-init="if (typeof Sortable !== 'undefined') new Sortable($el, { group: 'todo-groups', handle: '.eco-drag-handle', animation: 150, delay: 100, delayOnTouchOnly: true, onEnd: (evt) => { const itemIds = Array.from(evt.to.children).filter(child => child.dataset.id).map(child => child.dataset.id); $wire.moveItemToGroup(evt.item.dataset.id, evt.to.dataset.group, itemIds); } })"
                            style="min-height: 40px;">
                            @if($this->groupedPendingItems[$groupKey]->isEmpty())
                                <div class="todo-empty-group">{{ __('No Due Date') }}</div>
                            @endif
                            @foreach($this->groupedPendingItems[$groupKey] as $item)
                                @include('livewire.todo.task-row')
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        @endif

        @if($this->completedItems->isNotEmpty())
            <div class="card" style="border-radius: 28px; overflow: hidden;">
                <div style="padding: 16px 24px; background: rgba(0,0,0,0.02); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px;"><div class="badge badge-soft" style="font-weight: 800; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('Completed') }}</div><div style="flex: 1; height: 1px; background: var(--border-color);"></div></div>
                <div class="eco-grid-body">
                    @foreach($this->completedItems as $item)
                        @include('livewire.todo.task-row')
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <template x-teleport="body"><div class="mobile-only"><button wire:click="addItem('')" class="main-fab" title="{{ __('Add Task') }}"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14"/><path d="M12 5v14"/></svg></button></div></template>
</div>
