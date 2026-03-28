<div class="animate-in" x-data="{ editingListId: null }">
    <!-- Header -->
    <div class="flex-header">
        <div>
            <h2 class="responsive-title">Shopping</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-top: var(--space-1);">Manage your grocery and household lists</p>
        </div>
        <div>
            <button wire:click="addItem" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Add Item
            </button>
        </div>
    </div>

    <!-- List Selection & Management -->
    <div class="flex-cards" style="align-items: center; justify-content: space-between; margin-bottom: var(--space-8);">
        <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 300px;">
            <!-- List Selector Dropdown -->
            <div x-data="{ open: false }" style="position: relative;">
                <button @click="open = !open" class="btn" style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 8px 16px; border-radius: 12px; display: flex; align-items: center; gap: 10px; font-weight: 700; color: var(--text-main); height: 44px; box-shadow: var(--shadow-sm); cursor: pointer;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><path d="M3 12h18"/><path d="M3 6h18"/><path d="M3 18h18"/></svg>
                    <span style="font-size: 14px;">{{ $this->activeList->name ?? 'Select List' }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5;"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                
                <div x-show="open" @click.outside="open = false" style="position: absolute; top: calc(100% + 8px); left: 0; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 8px; min-width: 240px; box-shadow: var(--shadow-lg); z-index: 100;" x-transition x-cloak>
                    <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; padding: 8px 12px; border-bottom: 1px solid var(--border-color); margin-bottom: 4px;">Shopping Lists</div>
                    <div style="max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 2px;">
                        @foreach($this->lists as $list)
                            <div 
                                wire:click="selectList({{ $list->id }})" 
                                @click="open = false"
                                style="padding: 10px 12px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: var(--transition); {{ $activeListId == $list->id ? 'background: var(--primary-soft); color: var(--primary); font-weight: 700;' : 'font-weight: 600; color: var(--text-main);' }}"
                                class="nav-link-item"
                            >
                                <span style="font-size: 13px;">{{ $list->name }}</span>
                                @if($activeListId == $list->id)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div style="height: 1px; background: var(--border-color); margin: 6px 4px;"></div>
                    <button wire:click="addList" @click="open = false" style="width: 100%; padding: 10px 12px; border-radius: 10px; border: none; background: transparent; color: var(--primary); font-weight: 800; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: var(--transition);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        Create New List
                    </button>
                </div>
            </div>

            <!-- Active List Name Rename -->
            @if($activeListId)
                <div style="display: flex; align-items: center; gap: 8px;" x-data="{ listName: '{{ addslashes($this->activeList->name) }}' }" wire:key="shopping-list-name-{{ $activeListId }}">
                    <div style="display: inline-grid; align-items: center; min-width: 120px; position: relative;">
                        <!-- Hidden span to measure text width and make the grid cell shrink/grow -->
                        <span x-text="listName || 'List name...'" style="grid-area: 1/1; visibility: hidden; padding: 0 16px; white-space: pre; font-weight: 800; font-size: 14px; min-width: 120px;"></span>
                        <input 
                            type="text" 
                            x-model="listName"
                            style="grid-area: 1/1; width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 12px; padding: 10px 16px; font-weight: 800; color: var(--text-main); font-size: 14px; outline: none; height: 44px; transition: border-color 0.2s;"
                            @blur="$wire.updateListName({{ $activeListId }}, listName)"
                            @keydown.enter="$event.target.blur()"
                            placeholder="List name..."
                        >
                    </div>
                    <button 
                        wire:confirm="Are you sure you want to delete this list and all its items?"
                        wire:click="deleteList({{ $activeListId }})" 
                        style="background: var(--danger-soft); color: var(--danger); border: none; padding: 10px; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; height: 44px; width: 44px; transition: var(--transition);"
                        title="Delete current list"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    </button>
                </div>
            @endif
        </div>
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

    <!-- Footer Actions -->
    <div style="margin-top: var(--space-8); display: flex; justify-content: center; padding-bottom: 40px;" x-data="{ sorting: false, sorted: false, showFinishModal: false }">
        
        @if(!$isShopping)
            <!-- Start Shopping Button -->
            <button 
                type="button"
                x-on:click="sorting = true; $wire.startShopping().then(() => { sorting = false; sorted = true; setTimeout(() => { $wire.enterShoppingMode() }, 2000) })"
                x-bind:disabled="sorting || sorted"
                class="btn" 
                x-bind:style="sorted ? 'background: var(--success); color: white; padding: 14px 32px; border-radius: 16px; font-weight: 700; transition: all 0.3s; box-shadow: var(--shadow-lg); font-size: 14px;' : 'background: var(--primary); color: white; padding: 14px 32px; border-radius: 16px; font-weight: 700; transition: all 0.3s; box-shadow: var(--shadow-lg); font-size: 14px;'"
                title="Automatically sort based on Swedish store layout"
            >
                <span x-show="!sorting && !sorted" style="display: flex; align-items: center; gap: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.31-7.22H5.14"/></svg>
                    <span>Start Shopping</span>
                </span>
                <span x-show="sorting" x-cloak style="display: flex; align-items: center; gap: 12px;">
                    <span class="spinner-small"></span>
                    <span>Sorting List...</span>
                </span>
                <span x-show="sorted" x-cloak style="display: flex; align-items: center; gap: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>List Sorted!</span>
                </span>
            </button>
        @else
            <!-- Finish Shopping Button -->
            <button 
                type="button"
                x-on:click="showFinishModal = true"
                class="btn" 
                style="background: var(--success); color: white; padding: 14px 32px; border-radius: 16px; font-weight: 700; transition: all 0.3s; box-shadow: var(--shadow-lg); font-size: 14px;"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 12px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>Finish Shopping</span>
            </button>
        @endif

        <!-- Finish Shopping Modal -->
        <template x-if="showFinishModal">
            <div class="modal-overlay" x-on:click.self="showFinishModal = false" x-transition>
                <div class="modal-content" x-transition>
                    <div class="modal-title">Finish Shopping?</div>
                    <p class="modal-desc">What would you like to do with the items in this list?</p>
                    
                    <div class="modal-actions">
                        <button 
                            type="button"
                            class="btn-finish-confirm"
                            x-on:click="$wire.finishShopping(false).then(() => { showFinishModal = false; sorted = false })"
                        >
                            Remove Completed Items
                        </button>
                        
                        <button 
                            type="button"
                            class="btn-finish-clear"
                            x-on:click="if(confirm('This will wipe the entire list! Are you sure?')) { $wire.finishShopping(true).then(() => { showFinishModal = false; sorted = false }) }"
                        >
                            Clear Entire List
                        </button>
                        
                        <button 
                            type="button"
                            class="btn-finish-cancel"
                            x-on:click="showFinishModal = false"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </template>

    </div>

</div>
