<div class="animate-in" x-data="{ editingListId: null }">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-8); padding-bottom: var(--space-6); border-bottom: 1px solid var(--border-color);">
        <div>
            <h2 style="font-size: 2.5rem; font-weight: 900; letter-spacing: -0.02em; line-height: 1;">Todo</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-top: var(--space-1);">Organize your tasks and stay productive</p>
        </div>
        <div>
            @if($activeTodoId)
                <button wire:click="addItem('')" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Add Task
                </button>
            @endif
        </div>
    </div>

    <!-- Multi-List Tabs & Filters -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-8);">
        <div 
            style="display: flex; align-items: center; gap: 4px; overflow-x: auto; padding-bottom: 4px; flex: 1;" 
            id="todo-tabs" 
        data-type="lists"
        x-init="if (typeof Sortable !== 'undefined') new Sortable($el, {
            handle: '.sort-handle-list',
            animation: 150,
            delay: 100,
            delayOnTouchOnly: true,
            onEnd: (evt) => {
                let ids = Array.from($el.querySelectorAll('[wire\\:key]')).map(el => el.getAttribute('wire:key').split('-')[2]);
                $wire.handleListReorder(ids);
            }
        })"
    >
        @foreach($this->todos as $list)
            <div 
                class="nav-link {{ $activeTodoId == $list->id ? 'active' : '' }}" 
                style="cursor: pointer; padding: 10px 20px; min-width: 120px; flex-shrink: 0; position: relative; group"
                wire:key="list-tab-{{ $list->id }}"
                data-id="{{ $list->id }}"
                @click="$wire.selectTodo({{ $list->id }})"
            >
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div class="sort-handle-list" style="cursor: grab; opacity: 0.3;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </div>
                    @if($activeTodoId == $list->id)
                        <input 
                            type="text" 
                            value="{{ $list->name }}" 
                            style="background: transparent; border: none; font-weight: 700; color: inherit; width: 100%; outline: none;"
                            @blur="$wire.updateListName({{ $list->id }}, $event.target.value)"
                            @keydown.enter="$event.target.blur()"
                        >
                    @else
                        <span style="font-weight: 700; font-size: 14px;">{{ $list->name }}</span>
                    @endif

                    <button 
                        wire:confirm="Are you sure you want to delete this list and all its tasks?"
                        wire:click.stop="deleteTodo({{ $list->id }})" 
                        style="background: transparent; border: none; padding: 4px; color: var(--danger); opacity: 0; transition: opacity 0.2s; cursor: pointer;"
                        class="list-delete-btn"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
            </div>
        @endforeach
        </div>
        
        <!-- Header Actions: Add List & Filter -->
        <div style="display: flex; align-items: center; gap: 8px; margin-left: 16px;">
            @if($this->availableTags->isNotEmpty())
            <div style="position: relative;" x-data="{ open: false }">
                <button @click="open = !open" class="btn" style="background: var(--bg-card-alt); padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; color: var(--text-muted); display: flex; align-items: center; gap: 6px; border: 1px solid var(--border-color);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter
                    @if(count($selectedTags) > 0)
                        <span class="badge badge-soft" style="background: var(--primary); color: white; padding: 2px 6px; font-size: 10px;">{{ count($selectedTags) }}</span>
                    @endif
                </button>
                
                <div x-show="open" @click.outside="open = false" style="position: absolute; top: calc(100% + 8px); right: 0; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; min-width: 200px; box-shadow: var(--shadow-lg); z-index: 50;" x-transition x-cloak>
                    <div style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid var(--border-color);">Filter by Tag</div>
                    <div style="display: flex; flex-direction: column; gap: 6px; max-height: 200px; overflow-y: auto;">
                        @foreach($this->availableTags as $tag)
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: var(--text-color); cursor: pointer; padding: 4px; border-radius: 6px; transition: background 0.2s;">
                                <input type="checkbox" wire:model.live="selectedTags" value="{{ $tag }}" style="width: 16px; height: 16px; accent-color: var(--primary); border-radius: 4px; border: 1px solid var(--border-color);">
                                {{ $tag }}
                            </label>
                        @endforeach
                    </div>
                    @if(count($selectedTags) > 0)
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                            <button wire:click="$set('selectedTags', [])" style="width: 100%; padding: 6px; border: none; background: transparent; color: var(--danger); font-size: 12px; font-weight: 700; cursor: pointer; text-align: center;">Clear Filters</button>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <button wire:click="addList" class="eco-add-btn" style="width: 36px; height: 36px;" title="Add List">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            </button>
        </div>
    </div>

    @if($activeTodoId)
        <!-- Items Container -->
        <div style="display: flex; flex-direction: column; gap: 8px;">
            
            @if($this->pendingItems->isEmpty())
                <div class="card" style="border-radius: 28px; overflow: hidden; margin-bottom: 24px;">
                    <div style="padding: 40px; text-align: center; color: var(--text-muted); font-size: 14px; font-weight: 500;">
                        No pending tasks in this list.
                    </div>
                </div>
            @else
                @foreach(['overdue' => 'Overdue', 'today' => 'Due Today', 'upcoming' => 'Upcoming', 'no_date' => 'No Due Date'] as $groupKey => $groupLabel)
                    @if($this->groupedPendingItems[$groupKey]->isNotEmpty() || ($groupKey === 'no_date' && $this->pendingItems->isNotEmpty()))
                        
                        @if($this->groupedPendingItems['overdue']->isNotEmpty() || $this->groupedPendingItems['today']->isNotEmpty() || $this->groupedPendingItems['upcoming']->isNotEmpty())
                            @if($groupKey !== 'no_date' || $this->groupedPendingItems['no_date']->isNotEmpty())
                                <div style="padding: 16px 16px 8px 16px; display: flex; align-items: center; gap: 8px;">
                                    @if($groupKey === 'overdue')
                                        <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--danger);"></div>
                                    @elseif($groupKey === 'today')
                                        <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--warning);"></div>
                                    @elseif($groupKey === 'upcoming')
                                        <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary);"></div>
                                    @else
                                        <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--text-muted);"></div>
                                    @endif
                                    <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">
                                        {{ $groupLabel }} <span style="opacity: 0.5; margin-left: 4px;">{{ $this->groupedPendingItems[$groupKey]->count() }}</span>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <div class="card" style="border-radius: 28px; overflow: hidden; margin-bottom: 8px;">
                            <div class="todo-grid-header">
                                <div class="text-muted" style="text-align: center; font-size: 9px; font-weight: 800; text-transform: uppercase;">Sort</div>
                                <div class="text-muted" style="text-align: center; font-size: 9px; font-weight: 800; text-transform: uppercase;">Done</div>
                                <div class="text-muted" style="font-size: 9px; font-weight: 800; text-transform: uppercase; padding-left: 10px;">Task Description</div>
                                <div class="text-muted" style="text-align: center; font-size: 9px; font-weight: 800; text-transform: uppercase;">Del</div>
                            </div>
                            
                            <div class="eco-grid-body" 
                                data-group="{{ $groupKey }}"
                                x-init="if (typeof Sortable !== 'undefined') new Sortable($el, {
                                    group: 'todo-groups',
                                    handle: '.eco-drag-handle',
                                    animation: 150,
                                    delay: 100,
                                    delayOnTouchOnly: true,
                                    onEnd: (evt) => {
                                        let itemIds = Array.from(evt.to.children)
                                            .filter(child => child.dataset.id)
                                            .map(child => child.dataset.id);
                                        let targetGroup = evt.to.dataset.group;
                                        let itemId = evt.item.dataset.id;
                                        $wire.moveItemToGroup(itemId, targetGroup, itemIds);
                                    }
                                })"
                                style="min-height: 40px;"
                            >
                                @foreach($this->groupedPendingItems[$groupKey] as $item)
                                    <div class="todo-grid-row" wire:key="item-{{ $item->id }}" data-id="{{ $item->id }}">
                                        <div class="cell-handle">
                                            <div class="eco-drag-handle" style="cursor: grab; display: flex; align-items: center; justify-content: center; color: var(--text-muted); opacity: 0.3; width: 100%; height: 100%;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                                            </div>
                                        </div>
                                        
                                        <div class="cell-status">
                                            <button 
                                                wire:click="toggleItem({{ $item->id }})"
                                                class="completion-toggle"
                                                title="Click to complete task"
                                            ></button>
                                        </div>

                                        <div class="cell-name" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 6px 10px;">
                                            <input type="date" 
                                                value="{{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('Y-m-d') : '' }}"
                                                class="date-badge {{ $groupKey === 'overdue' ? 'overdue' : ($groupKey === 'today' ? 'today' : ($item->due_date ? 'upcoming' : 'empty')) }}"
                                                @change="$wire.updateItemDueDate({{ $item->id }}, $event.target.value)"
                                                title="Set due date"
                                            >
                                            
                                            <input 
                                                type="text" 
                                                value="{{ $item->name }}" 
                                                class="eco-inline-input"
                                                style="flex: 1; min-width: 120px;"
                                                placeholder="Task description..."
                                                @blur="$wire.updateItemName({{ $item->id }}, $event.target.value)"
                                                @keydown.enter="$event.target.blur()"
                                            >

                                            <input 
                                                type="text" 
                                                value="{{ $item->category }}" 
                                                class="category-badge"
                                                placeholder="+ tag"
                                                @blur="$wire.updateItemCategory({{ $item->id }}, $event.target.value)"
                                                @keydown.enter="$event.target.blur()"
                                            >
                                        </div>

                                        <div class="cell-delete">
                                            <button 
                                                wire:confirm="Are you sure you want to delete this task?"
                                                wire:click="deleteItem({{ $item->id }})" 
                                                class="eco-delete-btn" 
                                                style="opacity: 0.5;"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

            <!-- Completed Section -->
            @if($this->completedItems->isNotEmpty())
            <div class="card" style="border-radius: 28px; overflow: hidden; opacity: 0.7;">
                <div style="padding: 16px 24px; background: rgba(0,0,0,0.02); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px;">
                    <div class="badge badge-soft" style="font-weight: 800; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em;">Completed</div>
                    <div style="flex: 1; height: 1px; background: var(--border-color);"></div>
                </div>
                
                <div class="eco-grid-body">
                    @foreach($this->completedItems as $item)
                        <div class="todo-grid-row checked-row" wire:key="item-{{ $item->id }}" data-id="{{ $item->id }}">
                            <div class="cell-handle" style="color: var(--text-muted); opacity: 0.1;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                            </div>

                            <div class="cell-status">
                                <button 
                                    wire:click="toggleItem({{ $item->id }})"
                                    class="completion-toggle checked"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </button>
                            </div>

                            <div class="cell-name" style="display: flex; flex-direction: column; justify-content: center;">
                                <span style="font-weight: 600; color: var(--text-muted); text-decoration: line-through; text-decoration-thickness: 2px; line-height: 1.2;">{{ $item->name }}</span>
                                @if($item->completed_at)
                                    <span style="font-size: 11px; color: var(--text-muted); margin-top: 4px; opacity: 0.7; font-weight: 600;">
                                        Completed: {{ $item->completed_at->format('M j, Y') }}
                                    </span>
                                @endif
                            </div>

                            <div class="cell-delete">
                                <button 
                                    wire:confirm="Are you sure you want to delete this completed task?"
                                    wire:click="deleteItem({{ $item->id }})" 
                                    class="eco-delete-btn" 
                                    style="opacity: 0.4;"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    @else
        <!-- Empty State -->
        <div style="text-align: center; padding: 100px 0;">
            <div style="width: 80px; height: 80px; border-radius: 32px; background: var(--bg-card); border: 2px solid var(--border-color); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; color: var(--primary); box-shadow: var(--shadow-xl);">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
            <h2 style="font-size: 2rem; font-weight: 900; margin-bottom: 8px; letter-spacing: -0.02em;">No lists found</h2>
            <p style="color: var(--text-muted); font-size: 16px; margin-bottom: 40px; font-weight: 500;">Create your first todo list to start organizing tasks.</p>
            <button wire:click="addList" class="btn btn-primary" style="padding: 16px 40px; border-radius: 20px; font-weight: 800; font-size: 15px; box-shadow: var(--shadow-lg);">
                Create First List
            </button>
        </div>
    @endif
</div>
