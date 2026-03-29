<div>
    <!-- Header & Summary Row -->
    <div class="flex-header">
        <div>
            <h1 style="font-size: 2.25rem; font-weight: 900; margin-bottom: 4px;">{{ __('Current month') }}</h1>
            <p style="color: var(--text-muted); font-weight: 600;">{{ __('Track and manage your household finances.') }}</p>
        </div>

        <!-- View/Edit Toggle Button -->
        <div style="margin-left: auto; display: flex; align-items: center; gap: 12px;">
            <button wire:click="toggleEditMode" class="btn {{ $isEditing ? 'btn-success' : 'btn-primary' }}" style="display: flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 12px; font-weight: 800; border: none; cursor: pointer; transition: all 0.2s;">
                @if($isEditing)
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    {{ __('Finish Editing') }}
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    {{ __('Edit Mode') }}
                @endif
            </button>
        </div>

        <!-- Summary Cards (Top Right) -->
        <div class="header-stats">
            <div class="summary-card" style="min-width: 140px; padding: 10px 16px;">
                <p class="summary-label">{{ __('Direct (25th)') }}</p>
                <h2 class="summary-value" style="color: var(--warning); font-size: 1.25rem;">{{ number_format($this->totalDirectExpenses, 0, ',', ' ') }}</h2>
            </div>
            <div class="summary-card" style="min-width: 140px; padding: 10px 16px;">
                <p class="summary-label">{{ __('Delayed') }}</p>
                <h2 class="summary-value" style="color: var(--slate-500); font-size: 1.25rem;">{{ number_format($this->totalDelayedExpenses, 0, ',', ' ') }}</h2>
            </div>
            <div class="summary-card" style="min-width: 140px; padding: 10px 16px;">
                <p class="summary-label">{{ __('Income') }}</p>
                <h2 class="summary-value" style="color: var(--success); font-size: 1.25rem;">{{ number_format($this->totalIncome, 0, ',', ' ') }}</h2>
            </div>
            <div class="summary-card" style="min-width: 140px; padding: 10px 16px;">
                <p class="summary-label">{{ __('Expenses') }}</p>
                <h2 class="summary-value" style="color: var(--danger); font-size: 1.25rem;">{{ number_format($this->totalExpenses, 0, ',', ' ') }}</h2>
            </div>
            <div class="summary-card" style="min-width: 140px; padding: 10px 16px;">
                <p class="summary-label">{{ __('Monthly Savings') }}</p>
                <h2 class="summary-value" style="color: var(--primary); font-size: 1.25rem;">{{ number_format($this->totalSavings, 0, ',', ' ') }}</h2>
            </div>
            <div class="summary-card accent" style="min-width: 140px; padding: 10px 16px;">
                <p class="summary-label" style="color: rgba(255,255,255,0.7);">{{ __('Remaining') }}</p>
                <h2 class="summary-value" style="font-size: 1.25rem;">{{ number_format($this->remaining, 0, ',', ' ') }}</h2>
            </div>
        </div>
    </div>

    <!-- Income, Savings, and Categories -->
    <div class="flex-cards" style="margin-bottom: var(--space-8);">

        <!-- Income Card -->
        <div class="card" style="flex: 1 1 400px;">
            <div class="card-header">
                <h3 style="font-weight: 900;">{{ __('Income') }}</h3>
                @if($isEditing)
                <button wire:click="addIncomeRow" class="eco-add-btn" title="{{ __('Add income') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </button>
                @endif
            </div>
            <div class="eco-grid-table {{ !$isEditing ? 'view-mode' : '' }}">
                <div class="eco-grid-header {{ $isEditing ? 'dashboard-edit-grid' : 'income-grid' }}">
                    @if($isEditing) 
                        <div></div> {{-- Drag handle space --}}
                        <div></div> {{-- Dot space --}}
                        <div style="font-size: 11px; text-transform: uppercase;">{{ __('Name') }}</div>
                        <div></div> {{-- Empty space for alignment --}}
                        <div style="font-size: 11px; text-transform: uppercase;">{{ __('Recipient') }}</div>
                        <div style="font-size: 11px; text-transform: uppercase; text-align: right;">{{ __('Amount') }}</div>
                        <div></div> {{-- Delete space --}}
                    @else
                        <div></div>
                        <div>{{ __('Name') }}</div>
                        <div>{{ __('Recipient') }}</div>
                        <div style="text-align: right;">{{ __('Amount') }}</div>
                    @endif
                </div>
                <div class="eco-grid-body"
                    x-init="if (typeof Sortable !== 'undefined') new Sortable($el, {
                        handle: '.eco-drag-handle',
                        animation: 150,
                        onEnd: (evt) => {
                            let ids = Array.from($el.querySelectorAll('[wire\\:key]')).map(el => el.getAttribute('wire:key').split('-')[1]);
                            $wire.reorder('income', ids);
                        }
                    })">
                    @forelse($this->incomes as $income)
                    <div class="eco-grid-row {{ $isEditing ? 'dashboard-edit-grid' : 'income-grid' }}" wire:key="income-{{ $income->id }}">
                        @if($isEditing)
                            <div class="eco-drag-handle" style="cursor: grab; color: var(--slate-300); display: flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                            </div>
                            <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--success); justify-self: center;"></div>
                            <input type="text" class="eco-inline-input" value="{{ $income->name }}" placeholder="{{ __('Name') }}"
                                wire:change="updateIncome({{ $income->id }}, 'name', $event.target.value)" style="width: 100%;">
                            <div></div> {{-- Spacer for Alignment with Savings --}}
                            <select class="eco-inline-select" style="width: 100%;"
                                wire:change="updateIncome({{ $income->id }}, 'recipient_id', $event.target.value)">
                                <option value="" @if(!$income->recipient_id) selected @endif>{{ __('Recipient') }}</option>
                                @foreach($this->users as $user)
                                    <option value="{{ $user->id }}" @if($income->recipient_id == $user->id) selected @endif>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" class="eco-inline-input eco-inline-amount" value="{{ $income->amount }}" placeholder="0" style="width: 100%;"
                                wire:change="updateIncome({{ $income->id }}, 'amount', $event.target.value)">
                            <div style="display: flex; justify-content: flex-end;">
                                <button wire:click="deleteIncome({{ $income->id }})" class="eco-delete-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </div>
                        @else
                            <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--success); justify-self: center;" class="desktop-only"></div>
                            <div class="eco-mobile-stack">
                                <div class="eco-field">
                                    <span class="mobile-label">{{ __('Name') }}</span>
                                    <span style="font-weight: 700; padding: 6px 0;">{{ $income->name ?: '—' }}</span>
                                </div>
                                <div class="eco-field">
                                    <span class="mobile-label">{{ __('Recipient') }}</span>
                                    <span style="font-weight: 600; color: var(--text-muted); font-size: 13px;">
                                        {{ $this->users->find($income->recipient_id)?->name ?: '—' }}
                                    </span>
                                </div>
                            </div>
                            <div class="eco-field">
                                <span class="mobile-label">{{ __('Amount') }}</span>
                                <div style="text-align: right; font-weight: 900; font-family: var(--font-heading); color: var(--success);">
                                    {{ number_format($income->amount, 0, ',', ' ') }} kr
                                </div>
                            </div>
                        @endif
                    </div>
                    @empty
                    <div style="text-align: center; color: var(--slate-400); padding: 20px; font-size: 13px;">{{ __('No income recorded.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Monthly Savings Card -->
        <div class="card" style="flex: 1 1 400px;">
            <div class="card-header">
                <h3 style="font-weight: 900;">{{ __('Monthly Savings') }}</h3>
                @if($isEditing)
                <button wire:click="addSavingRow" class="eco-add-btn" title="{{ __('Add saving') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </button>
                @endif
            </div>
            <div class="eco-grid-table {{ !$isEditing ? 'view-mode' : '' }}">
                <div class="eco-grid-header {{ $isEditing ? 'dashboard-edit-grid' : 'income-grid' }}">
                    @if($isEditing) 
                        <div></div> {{-- Drag handle space --}}
                        <div></div> {{-- Dot space --}}
                        <div style="font-size: 11px; text-transform: uppercase;">{{ __('Purpose') }}</div>
                        <div style="font-size: 11px; text-transform: uppercase;">{{ __('Bank/App') }}</div>
                        <div style="font-size: 11px; text-transform: uppercase;">{{ __('Saver') }}</div>
                        <div style="font-size: 11px; text-transform: uppercase; text-align: right;">{{ __('Amount') }}</div>
                        <div></div> {{-- Delete space --}}
                    @else
                        <div></div>
                        <div>{{ __('Purpose') }}</div>
                        <div>{{ __('Bank/Saver') }}</div>
                        <div style="text-align: right;">{{ __('Amount') }}</div>
                    @endif
                </div>
                <div class="eco-grid-body"
                    x-init="new Sortable($el, {
                        handle: '.eco-drag-handle',
                        animation: 150,
                        onEnd: (evt) => {
                            let ids = Array.from($el.querySelectorAll('[wire\\:key]')).map(el => el.getAttribute('wire:key').split('-')[1]);
                            $wire.reorder('saving', ids);
                        }
                    })">
                    @forelse($this->savings as $saving)
                    <div class="eco-grid-row {{ $isEditing ? 'dashboard-edit-grid' : 'income-grid' }}" wire:key="saving-{{ $saving->id }}">
                        @if($isEditing)
                            <div class="eco-drag-handle" style="cursor: grab; color: var(--slate-300); display: flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                            </div>
                            <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary); justify-self: center;"></div>
                            <input type="text" class="eco-inline-input" value="{{ $saving->name }}" placeholder="{{ __('Purpose') }}"
                                wire:change="updateSaving({{ $saving->id }}, 'name', $event.target.value)" style="width: 100%;">
                            <input type="text" class="eco-inline-input" value="{{ $saving->location }}" placeholder="{{ __('Bank/App') }}"
                                wire:change="updateSaving({{ $saving->id }}, 'location', $event.target.value)" style="width: 100%;">
                            <select class="eco-inline-select" style="width: 100%;"
                                wire:change="updateSaving({{ $saving->id }}, 'saver_id', $event.target.value)">
                                <option value="" @if(!$saving->saver_id) selected @endif>{{ __('Saver') }}</option>
                                @foreach($this->users as $user)
                                    <option value="{{ $user->id }}" @if($saving->saver_id == $user->id) selected @endif>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" class="eco-inline-input eco-inline-amount" value="{{ $saving->amount }}" placeholder="0" style="width: 100%;"
                                wire:change="updateSaving({{ $saving->id }}, 'amount', $event.target.value)">
                            <div style="display: flex; justify-content: flex-end;">
                                <button wire:click="deleteSaving({{ $saving->id }})" class="eco-delete-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </div>
                        @else
                            <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary); justify-self: center;" class="desktop-only"></div>
                            <div class="eco-mobile-stack">
                                <div class="eco-field">
                                    <span class="mobile-label">{{ __('Name') }}</span>
                                    <span style="font-weight: 700; padding: 6px 0;">{{ $saving->name ?: '—' }}</span>
                                </div>

                                <div class="eco-field">
                                    <span class="mobile-label">{{ __('Bank/Saver') }}</span>
                                    <div style="min-width: 0; display: flex; flex-direction: column;">
                                        <span style="font-size: 13px; font-weight: 600; color: var(--text-muted); line-height: 1.2;">
                                            {{ $saving->location ?: '—' }}
                                        </span>
                                        <span style="font-size: 11px; color: var(--text-muted); opacity: 0.8; line-height: 1.2;">
                                            {{ $this->users->find($saving->saver_id)?->name ?: '—' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="eco-field">
                                <span class="mobile-label">{{ __('Amount') }}</span>
                                <div style="text-align: right; min-width: 0;">
                                    <div style="font-weight: 900; font-family: var(--font-heading); color: var(--primary);">
                                        {{ number_format($saving->amount, 0, ',', ' ') }} kr
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    @empty
                    <div style="text-align: center; color: var(--slate-400); padding: 20px; font-size: 13px;">{{ __('No savings recorded.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Categories Card -->
        <div class="card" style="flex: 0 0 360px;">
            <div class="card-header">
                <div>
                    <h3 style="font-weight: 900;">{{ __('Categories') }}</h3>
                </div>
                @if($isEditing)
                <button wire:click="addExpenseCategoryRow" class="eco-add-btn" title="{{ __('Add category') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </button>
                @endif
            </div>
            <div class="eco-grid-table {{ !$isEditing ? 'view-mode' : '' }}">
                <div class="eco-grid-header categories-grid">
                    @if($isEditing) <div style="width: 20px;"></div> @endif
                    <div></div>
                    <div>{{ __('Name') }}</div>
                    <div style="text-align: right;">{{ __('Total Sum') }}</div>
                    @if($isEditing) <div style="width: 40px;"></div> @endif
                </div>
                <div class="eco-grid-body"
                    x-init="new Sortable($el, {
                        handle: '.eco-drag-handle',
                        animation: 150,
                        onEnd: (evt) => {
                            let ids = Array.from($el.querySelectorAll('[wire\\:key]')).map(el => el.getAttribute('wire:key').split('-')[1]);
                            $wire.reorder('category', ids);
                        }
                    })">
                    @forelse($this->expenseCategories as $category)
                    @php
                        $catTotal = $this->expenses->where('category', $category->name)->sum('amount');
                    @endphp
                    <div class="eco-grid-row categories-grid" wire:key="category-{{ $category->id }}">
                        @if($isEditing)
                        <div class="eco-drag-handle" style="cursor: grab; color: var(--slate-300); display: flex; align-items: center; justify-content: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                        </div>
                        @endif
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary); justify-self: center;"></div>
                        @if($isEditing)
                            <input type="text" class="eco-inline-input" value="{{ $category->name }}" placeholder="{{ __('Category name') }}"
                                wire:change="updateExpenseCategory({{ $category->id }}, 'name', $event.target.value)">
                        @else
                            <span style="font-weight: 700; padding: 6px 0;">{{ $category->name ?: '—' }}</span>
                        @endif
                        <div style="font-size: 12px; font-weight: 900; color: var(--text-main); text-align: right; font-family: var(--font-heading);">
                            {{ number_format($catTotal, 0, ',', ' ') }} kr
                        </div>
                        @if($isEditing)
                        <div style="display: flex; justify-content: flex-end;">
                            <button wire:click="deleteExpenseCategory({{ $category->id }})" class="eco-delete-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div style="text-align: center; color: var(--slate-400); padding: 20px; font-size: 13px;">{{ __('No categories yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- Expenses — full width -->
    <div class="card">
        <div class="card-header" style="border-radius: 24px 24px 0 0;">
            <div>
                <h3 style="font-weight: 900;">{{ __('Expenses') }}</h3>
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <p style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--slate-400); margin: 0;">{{ __('Recurring & one-time') }}</p>
                    <div style="display: flex; gap: 8px; align-items: center; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                        <span style="display: flex; gap: 4px; align-items: center; color: var(--primary);">
                            <span style="width: 5px; height: 5px; border-radius: 50%; background: var(--primary);"></span>
                            {{ __('Split: Divide equally') }}
                        </span>
                        <span style="display: flex; gap: 4px; align-items: center; color: var(--warning);">
                            <span style="width: 5px; height: 5px; border-radius: 50%; background: var(--warning);"></span>
                            {{ __('Delay: Pay later (not 25th)') }}
                        </span>
                    </div>
                </div>
            </div>
            @if($isEditing)
            <button wire:click="addExpenseRow" class="eco-add-btn" title="{{ __('Add expense') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            </button>
            @endif
        </div>

        <div class="eco-grid-table {{ !$isEditing ? 'view-mode' : '' }}">
            <div class="eco-grid-header expenses-grid">
                @if($isEditing) <div style="width: 20px;"></div> @endif
                <div>{{ __('Name') }}</div>
                <div>{{ __('Category') }}</div>
                <div>{{ __('Payer(s)') }}</div>
                <div>{{ __('Handling') }}</div>
                <div>{{ __('Options') }}</div>
                <div style="text-align: right;">{{ __('Amount') }}</div>
                @if($isEditing) <div></div> @endif
            </div>
            <div class="eco-grid-body"
                x-init="if (typeof Sortable !== 'undefined') new Sortable($el, {
                    handle: '.eco-drag-handle',
                    animation: 150,
                    onEnd: (evt) => {
                        let ids = Array.from($el.querySelectorAll('[wire\\:key]')).map(el => el.getAttribute('wire:key').split('-')[1]);
                        $wire.reorder('expense', ids);
                    }
                })">
                @forelse($this->expenses as $expense)
                <div class="eco-grid-row expenses-grid" wire:key="expense-{{ $expense->id }}">
                    @if($isEditing)
                        {{-- Edit Mode: 7-column grid on desktop, functional fields on mobile --}}
                        <div class="eco-row-header">
                            <div class="eco-drag-handle" style="cursor: grab; color: var(--slate-300); display: flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                            </div>
                            <button wire:click="deleteExpense({{ $expense->id }})" class="eco-delete-btn mobile-only">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                        <div class="eco-mobile-row">
                            <div class="eco-field">
                                <span class="mobile-label">{{ __('Name') }}</span>
                                <input type="text" class="eco-inline-input" value="{{ $expense->name }}" placeholder="{{ __('Expense name') }}"
                                    wire:change="updateExpense({{ $expense->id }}, 'name', $event.target.value)">
                            </div>
                            <div class="eco-field">
                                <span class="mobile-label">{{ __('Category') }}</span>
                                <select class="eco-inline-select" style="width: 100%;"
                                    wire:change="updateExpense({{ $expense->id }}, 'category', $event.target.value)">
                                    <option value="" @if(!$expense->category) selected @endif>—</option>
                                    @foreach($this->expenseCategories as $cat)
                                        <option value="{{ $cat->name }}" @if($expense->category === $cat->name) selected @endif>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="eco-field" style="overflow: visible;">
                            <span class="mobile-label">{{ __('Payer(s)') }}</span>
                            <div class="eco-payer-dropdown" x-data="{ open: false, dropUp: false }" @click.outside="open = false" style="width: 100%;">
                                <button type="button" class="eco-payer-trigger"
                                    @click="open = !open; if(open) dropUp = (window.innerHeight - $el.getBoundingClientRect().bottom) < 220"
                                    style="width: 100%; justify-content: space-between;">
                                    @if(count($expense->payer_ids ?? []) > 0)
                                        <span class="eco-payer-tags" style="flex: 1; overflow: hidden;">
                                            @foreach($expense->payer_ids as $pid)
                                                @php $pUser = $this->users->find($pid); @endphp
                                                @if($pUser)
                                                    <span class="eco-payer-tag">{{ $pUser->name }}</span>
                                                @endif
                                            @endforeach
                                        </span>
                                    @else
                                        <span class="eco-payer-placeholder">{{ __('None') }}</span>
                                    @endif
                                    <svg class="eco-payer-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div class="eco-payer-menu" x-show="open" :class="{ 'eco-drop-up': dropUp }" x-transition.opacity.duration.150ms>
                                    @foreach($this->users as $user)
                                    <label class="eco-payer-option">
                                        <input type="checkbox"
                                            @if(in_array($user->id, $expense->payer_ids ?? [])) checked @endif
                                            wire:click="toggleExpensePayer({{ $expense->id }}, {{ $user->id }})">
                                        <span>{{ $user->name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="eco-mobile-row">
                            <div class="eco-field">
                                <span class="mobile-label">{{ __('Handling') }}</span>
                                <select class="eco-inline-select" style="width: 100%;"
                                    wire:change="updateExpense({{ $expense->id }}, 'handling', $event.target.value)">
                                    <option value="Autogiro" @if($expense->handling === 'Autogiro') selected @endif>{{ __('Autogiro') }}</option>
                                    <option value="Manuellt" @if($expense->handling === 'Manuellt') selected @endif>{{ __('Manuellt') }}</option>
                                    <option value="E-faktura" @if($expense->handling === 'E-faktura') selected @endif>{{ __('E-faktura') }}</option>
                                    <option value="Swish" @if($expense->handling === 'Swish') selected @endif>{{ __('Swish') }}</option>
                                </select>
                            </div>
                            <div class="eco-field">
                                <span class="mobile-label">{{ __('Options') }}</span>
                                <div style="display: flex; gap: 8px; align-items: center; height: 100%;">
                                    <label class="eco-toggle-label" title="{{ __('Split cost') }}">
                                        <input type="checkbox" @if($expense->split) checked @endif
                                            wire:change="updateExpense({{ $expense->id }}, 'split', $event.target.checked ? 1 : 0)">
                                        <span class="eco-toggle-text">{{ __('Split') }}</span>
                                    </label>
                                    <label class="eco-toggle-label" title="{{ __('Delayed payment') }}">
                                        <input type="checkbox" @if($expense->delayed) checked @endif
                                            wire:change="updateExpense({{ $expense->id }}, 'delayed', $event.target.checked ? 1 : 0)">
                                        <span class="eco-toggle-text eco-toggle-warn">{{ __('Delay') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="eco-field" style="text-align: right;">
                            <span class="mobile-label">{{ __('Amount') }}</span>
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 4px;">
                                <input type="number" class="eco-inline-input eco-inline-amount" value="{{ $expense->amount }}" placeholder="0" style="width: 100%;"
                                    wire:change="updateExpense({{ $expense->id }}, 'amount', $event.target.value)">
                            </div>
                        </div>
                        {{-- Desktop Delete Button Column (7th child) --}}
                        <div class="desktop-only" style="display: flex; justify-content: flex-end; align-items: center;">
                            <button wire:click="deleteExpense({{ $expense->id }})" class="eco-delete-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                    @else
                        {{-- New Premium Summary Card for View Mode (Mobile Only) --}}
                        <div class="mobile-only" style="width: 100%;">
                            <div class="expense-view-header">
                                <div class="expense-view-name">{{ $expense->name ?: '—' }}</div>
                                <div class="expense-view-amount">{{ number_format($expense->amount, 0, ',', ' ') }} kr</div>
                            </div>
                            <div class="expense-view-category">{{ $expense->category ?: __('No category') }}</div>

                            @php $payerFound = false; @endphp
                            @foreach($expense->payer_ids as $pid)
                                @if($this->users->find($pid)) @php $payerFound = true; break; @endphp @endif
                            @endforeach

                            @if($payerFound)
                                <div class="expense-view-details" style="grid-template-columns: 1fr;">
                                    <div class="expense-detail-item">
                                        <span class="expense-detail-label">{{ __('Payer(s)') }}</span>
                                        <div class="eco-payer-tags" style="margin-top: 2px;">
                                            @foreach($expense->payer_ids as $pid)
                                                @php $pUser = $this->users->find($pid); @endphp
                                                @if($pUser)
                                                    <span class="eco-payer-tag" style="line-height: 1.2; padding: 2px 8px;">{{ $pUser->name }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Standard Grid Layout for Desktop (View Mode) --}}
                        <div class="force-desktop-grid">
                            <div class="eco-field">
                                <span style="font-weight: 700;">{{ $expense->name ?: '—' }}</span>
                            </div>
                            <div class="eco-field">
                                <span style="font-weight: 600; color: var(--text-muted); font-size: 13px;">{{ $expense->category ?: '—' }}</span>
                            </div>
                            <div class="eco-field">
                                <div class="eco-payer-tags">
                                    @foreach($expense->payer_ids as $pid)
                                        @php $pUser = $this->users->find($pid); @endphp
                                        @if($pUser) <span class="eco-payer-tag">{{ $pUser->name }}</span> @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="eco-field">
                                <span style="font-weight: 600; color: var(--text-muted); font-size: 13px;">{{ $expense->handling ?: '—' }}</span>
                            </div>
                            <div class="eco-field">
                                <div style="display: flex; gap: 8px;">
                                    @if($expense->split)
                                        <span class="eco-payer-tag" style="background: var(--blue-500); color: white; font-size: 10px;">{{ __('DELA') }}</span>
                                    @endif
                                    @if($expense->delayed)
                                        <span class="eco-payer-tag" style="background: var(--amber-500); color: white; font-size: 10px;">{{ __('DRÖJSMÅL') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="eco-field" style="text-align: right;">
                                <div style="font-weight: 900; font-family: var(--font-heading); color: var(--danger);">
                                    {{ number_format($expense->amount, 0, ',', ' ') }} kr
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                @empty
                <div style="text-align: center; color: var(--slate-400); padding: 40px; font-size: 13px;">{{ __('No expenses recorded. Click + to add one.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

