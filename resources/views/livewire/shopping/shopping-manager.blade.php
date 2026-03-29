<div class="animate-in" x-data="{ editingListId: null }"
    @shopping-item-added.window="$nextTick(() => { 
        let input = document.getElementById('shopping-item-input-' + $event.detail.itemId);
        if (input) {
            input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => input.focus(), 100);
        }
    })">
    @if (session()->has('message'))
    <template x-teleport="body">
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
            style="position: fixed; top: 24px; left: 50%; transform: translateX(-50%); z-index: 2100; background: var(--success); color: white; padding: 12px 24px; border-radius: 16px; font-weight: 800; box-shadow: var(--shadow-xl); display: flex; align-items: center; gap: 12px;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            {{ session('message') }}
        </div>
    </template>
    @endif
    <!-- Header -->
    <div class="flex-header">
        <div>
            <h2 class="responsive-title">{{ __('Shopping') }}</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-top: var(--space-1);">{{ __('Manage your grocery and household lists') }}</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <button wire:click="sortItems" class="btn desktop-only" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--primary); display: flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 16 4 4 4-4"/><path d="M7 20V4"/><path d="M11 4h10"/><path d="M11 8h7"/><path d="M11 12h4"/></svg>
                {{ __('Sort List') }}
            </button>
            <button wire:click="addItem" class="btn btn-primary desktop-only" style="display: flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                {{ __('Add Item') }}
            </button>
        </div>
    </div>

    <!-- List Selection & Management -->
    <div class="flex-cards" style="align-items: center; justify-content: space-between; margin-bottom: var(--space-8);">
        <div class="manager-actions-row">
            <!-- List Selector Dropdown -->
            <div x-data="{ open: false }" style="position: relative;">
                <button @click="open = !open" class="btn" style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 8px 16px; border-radius: 12px; display: flex; align-items: center; gap: 10px; font-weight: 700; color: var(--text-main); height: 44px; box-shadow: var(--shadow-sm); cursor: pointer;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><path d="M3 12h18"/><path d="M3 6h18"/><path d="M3 18h18"/></svg>
                    <span style="font-size: 14px;">{{ $this->activeList->name ?? __('Select List') }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5;"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                
                <div x-show="open" @click.outside="open = false" style="position: absolute; top: calc(100% + 8px); left: 0; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 8px; min-width: 240px; box-shadow: var(--shadow-lg); z-index: 100;" x-transition x-cloak>
                    <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; padding: 8px 12px; border-bottom: 1px solid var(--border-color); margin-bottom: 4px;">{{ __('Shopping Lists') }}</div>
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
                        {{ __('Create New List') }}
                    </button>
                </div>
            </div>

            <!-- Active List Name Rename -->
            @if($activeListId)
                <div style="display: flex; align-items: center; gap: 8px;" x-data="{ listName: '{{ addslashes($this->activeList->name) }}' }" wire:key="shopping-list-name-{{ $activeListId }}">
                    <div style="display: inline-grid; align-items: center; min-width: 120px; position: relative;">
                        <!-- Hidden span to measure text width and make the grid cell shrink/grow -->
                        <span x-text="listName || '{{ __('List name...') }}'" style="grid-area: 1/1; visibility: hidden; padding: 0 16px; white-space: pre; font-weight: 800; font-size: 14px; min-width: 120px;"></span>
                        <input 
                            type="text" 
                            x-model="listName"
                            style="grid-area: 1/1; width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 12px; padding: 10px 16px; font-weight: 800; color: var(--text-main); font-size: 14px; outline: none; height: 44px; transition: border-color 0.2s;"
                            @blur="$wire.updateListName({{ $activeListId }}, listName)"
                            @keydown.enter="$event.target.blur()"
                            placeholder="{{ __('List name...') }}"
                        >
                    </div>
                    <button 
                        wire:confirm="{{ __('Are you sure you want to delete this list and all its items?') }}"
                        wire:click="deleteList({{ $activeListId }})" 
                        style="background: var(--danger-soft); color: var(--danger); border: none; padding: 10px; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; height: 44px; width: 44px; transition: var(--transition);"
                        title="{{ __('Delete current list') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    </button>
                </div>
            @endif
        </div>
    </div>



    <!-- Items List -->
    <div class="card" style="border-radius: 28px; overflow: hidden;">
        <div class="eco-grid-table">
            <div class="eco-grid-header shopping-header shopping-grid-header">
                <div class="desktop-only text-muted" style="text-align: center; font-size: 9px; font-weight: 800; text-transform: uppercase;">{{ __('Sort') }}</div>
                <div class="text-muted" style="text-align: center; font-size: 9px; font-weight: 800; text-transform: uppercase;">{{ __('Done') }}</div>
                <div class="text-muted" style="font-size: 9px; font-weight: 800; text-transform: uppercase; padding-left: 10px;">{{ __('Item Name & Qty') }}</div>
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


                        <!-- Sort Handle -->
                        <div class="eco-drag-handle cell-handle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                        </div>

                        <!-- Check Status (Completion) -->
                        <div class="cell-status">
                            <button 
                                wire:click="toggleCheck({{ $item->id }})" 
                                class="completion-toggle {{ $item->is_checked ? 'checked' : '' }}" 
                                title="{{ __('Click to complete item') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" style="display: {{ $item->is_checked ? 'block' : 'none' }}"><polyline points="20 6 9 17 4 12"/></svg>
                            </button>
                        </div>

                        <!-- Name & Quantity -->
                        <div class="cell-name" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-right: 12px;">
                            <input 
                                type="text" 
                                value="{{ $item->name }}" 
                                id="shopping-item-input-{{ $item->id }}"
                                class="eco-inline-input" 
                                style="{{ $item->is_checked ? 'text-decoration: line-through; opacity: 0.5;' : '' }} flex: 1; min-width: 0;"
                                @blur="$wire.updateItem({{ $item->id }}, 'name', $event.target.value)"
                                @keydown.enter="$event.target.blur()"
                            >
                            <div class="quantity-stepper" style="flex-shrink: 0; transform: scale(0.9); transform-origin: right center;">
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
                        <div style="font-weight: 700;">{{ __('No items in this list') }}</div>
                        <div style="font-size: 13px; margin-top: 4px;">{{ __('Add something to buy!') }}</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Mobile Floating Action Buttons -->
    @if($activeListId)
    <template x-teleport="body">
        <div class="mobile-only">
            <!-- Sort FAB -->
            <button wire:click="sortItems" class="main-fab main-fab-secondary" title="{{ __('Sort List') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 16 4 4 4-4"/><path d="M7 20V4"/><path d="M11 4h10"/><path d="M11 8h7"/><path d="M11 12h4"/></svg>
            </button>

            <!-- Add FAB -->
            <button wire:click="addItem" class="main-fab" title="{{ __('Add Item') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            </button>
        </div>
    </template>
    @endif
</div>

