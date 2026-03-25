<div class="animate-in" x-data="{ editingListId: null }">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-8); padding-bottom: var(--space-6); border-bottom: 1px solid var(--border-color);">
        <div>
            <div class="badge badge-soft" style="color: var(--primary); background: var(--primary-soft); margin-bottom: var(--space-2);">Navigation</div>
            <h2 style="font-size: 2.5rem; font-weight: 900; letter-spacing: -0.02em; line-height: 1;">Shopping</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-top: var(--space-1);">Manage your grocery and household lists</p>
        </div>
        <div>
            <button wire:click="addItem" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Add Item
            </button>
        </div>
    </div>

    <!-- Multi-List Tabs -->
    <div style="display: flex; align-items: center; gap: 4px; margin-bottom: var(--space-8); overflow-x: auto; padding-bottom: 4px;" id="list-tabs" data-type="lists">
        @foreach($this->lists as $list)
            <div 
                class="nav-link {{ $activeListId == $list->id ? 'active' : '' }}" 
                style="cursor: pointer; padding: 10px 20px; min-width: 120px; flex-shrink: 0; position: relative;"
                wire:key="list-tab-{{ $list->id }}"
                data-id="{{ $list->id }}"
                @click="$wire.selectList({{ $list->id }})"
            >
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div class="sort-handle-list" style="cursor: grab; opacity: 0.3;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </div>
                    @if($activeListId == $list->id)
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
                </div>
            </div>
        @endforeach
        
        <button wire:click="addList" class="eco-add-btn" style="margin-left: 8px; width: 36px; height: 36px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        </button>
    </div>

    <!-- Bulk Actions Bar -->
    @if(count($selectedItems) > 0)
        <div class="animate-in bulk-actions-bar">
            <div class="bulk-info">
                <div class="badge-selected">{{ count($selectedItems) }} Selected</div>
                <span class="bulk-text">Multiple items ready for action</span>
            </div>
            <div class="bulk-btns">
                <button wire:click="bulkToggleCheck" class="btn btn-bulk-toggle">Toggle Status</button>
                <button wire:click="bulkDelete" class="btn btn-bulk-delete">Delete Selected</button>
            </div>
        </div>
    @endif

    <!-- Items List -->
    <div class="card" style="border-radius: 28px; overflow: hidden;">
        <div class="eco-grid-table">
            <div class="eco-grid-header shopping-header shopping-grid-header" style="grid-template-columns: 40px 40px 50px 1fr 140px 40px;">
                <button wire:click="toggleSelectAll" title="Select all items" style="background: transparent; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: {{ count($selectedItems) === count($this->items) && count($this->items) > 0 ? 'var(--primary)' : 'var(--text-muted)' }}; transition: all 0.2s;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: {{ count($selectedItems) > 0 ? '1' : '0.5' }};">
                        @if(count($selectedItems) === count($this->items) && count($this->items) > 0)
                            <rect width="18" height="18" x="3" y="3" rx="2" fill="currentColor" fill-opacity="0.1"/>
                            <polyline points="9 11 12 14 22 4" stroke-width="4"/>
                        @else
                            <rect width="18" height="18" x="3" y="3" rx="2"/>
                        @endif
                    </svg>
                </button>
                <div class="desktop-only text-muted" style="text-align: center; font-size: 9px; font-weight: 800; text-transform: uppercase;">Sort</div>
                <div class="text-muted" style="text-align: center; font-size: 9px; font-weight: 800; text-transform: uppercase;">Done</div>
                <div class="text-muted" style="font-size: 9px; font-weight: 800; text-transform: uppercase;">Item Name</div>
                <div class="text-muted" style="text-align: center; font-size: 9px; font-weight: 800; text-transform: uppercase;">Quantity</div>
                <div class="desktop-only"></div>
            </div>
            <div class="eco-grid-body" 
                id="shopping-items-list" 
                data-type="items"
                x-init="if (typeof Sortable !== 'undefined') new Sortable($el, {
                    handle: '.eco-drag-handle',
                    animation: 150,
                    onEnd: (evt) => {
                        let ids = Array.from($el.querySelectorAll('[wire\\:key]')).map(el => el.getAttribute('wire:key').split('-')[1]);
                        $wire.handleReorder('items', ids);
                    }
                })"
            >
                @forelse($this->items as $item)
                    <div 
                        class="eco-grid-row shopping-grid-row {{ $item->is_checked ? 'checked-row' : '' }}" 
                        wire:key="item-{{ $item->id }}"
                        data-id="{{ $item->id }}"
                    >
                        <!-- Bulk Select -->
                        <div class="cell-select">
                            <input type="checkbox" wire:model.live="selectedItems" value="{{ $item->id }}" class="bulk-checkbox">
                        </div>

                        <!-- Sort Handle -->
                        <div class="eco-drag-handle cell-handle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                        </div>

                        <!-- Check Status (Completion) -->
                        <div class="cell-status">
                            <button 
                                wire:click="toggleCheck({{ $item->id }})" 
                                class="completion-toggle {{ $item->is_checked ? 'checked' : '' }}" 
                                title="Click to complete item"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" style="display: {{ $item->is_checked ? 'block' : 'none' }}"><polyline points="20 6 9 17 4 12"/></svg>
                            </button>
                        </div>

                        <!-- Name -->
                        <div class="cell-name">
                            <input 
                                type="text" 
                                value="{{ $item->name }}" 
                                class="eco-inline-input" 
                                style="{{ $item->is_checked ? 'text-decoration: line-through; opacity: 0.5;' : '' }}"
                                @blur="$wire.updateItem({{ $item->id }}, 'name', $event.target.value)"
                                @keydown.enter="$event.target.blur()"
                            >
                        </div>

                        <!-- Quantity -->
                        <div class="cell-qty">
                            <div class="quantity-stepper">
                                <button wire:click="decrementQuantity({{ $item->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
                                </button>
                                <span class="qty-val">{{ $item->quantity }}x</span>
                                <button wire:click="incrementQuantity({{ $item->id }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Delete -->
                        <div class="cell-delete">
                            <button wire:click="deleteItem({{ $item->id }})" class="eco-delete-btn" style="opacity: 0.5;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div style="padding: 60px; text-align: center; color: var(--text-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; opacity: 0.3;"><path d="m15 11-1 9"/><path d="m19 11-4-7"/><path d="M2 11h20"/><path d="m3.5 11 1.6 9c.2 1 1.2 2 2.1 2h9.6c.9 0 1.9-1 2.1-2l1.6-9"/><path d="m5 11 4-7"/><path d="M9 11v9"/></svg>
                        <div style="font-weight: 700;">No items in this list</div>
                        <div style="font-size: 13px; margin-top: 4px;">Add something to buy!</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:navigated', () => {
            const listTabs = document.getElementById('list-tabs');
            if (listTabs && typeof Sortable !== 'undefined') {
                new Sortable(listTabs, {
                    handle: '.sort-handle-list',
                    animation: 150,
                    onEnd: (evt) => {
                        let ids = Array.from(listTabs.children)
                            .filter(child => child.dataset.id)
                            .map(child => child.dataset.id);
                        @this.handleReorder('lists', ids);
                    }
                });
            }
        });
    </script>
    @endpush
</div>
