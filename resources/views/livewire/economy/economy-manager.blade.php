<div>
    <!-- Header & Summary Row -->
    <div class="flex-header">
        <div>
            <h1 style="font-size: 2.25rem; font-weight: 900; margin-bottom: 4px;">Current Month</h1>
            <p style="color: var(--text-muted); font-weight: 600;">Track and manage your household finances.</p>
        </div>

        <!-- Summary Cards (Top Right) -->
        <div class="header-stats">
            <div class="summary-card" style="min-width: 140px; padding: 10px 16px;">
                <p class="summary-label">Direct (25th)</p>
                <h2 class="summary-value" style="color: var(--warning); font-size: 1.25rem;">{{ number_format($this->totalDirectExpenses, 0, ',', ' ') }}</h2>
            </div>
            <div class="summary-card" style="min-width: 140px; padding: 10px 16px;">
                <p class="summary-label">Delayed</p>
                <h2 class="summary-value" style="color: var(--slate-500); font-size: 1.25rem;">{{ number_format($this->totalDelayedExpenses, 0, ',', ' ') }}</h2>
            </div>
            <div class="summary-card" style="min-width: 140px; padding: 10px 16px;">
                <p class="summary-label">Income</p>
                <h2 class="summary-value" style="color: var(--success); font-size: 1.25rem;">{{ number_format($this->totalIncome, 0, ',', ' ') }}</h2>
            </div>
            <div class="summary-card" style="min-width: 140px; padding: 10px 16px;">
                <p class="summary-label">Expenses</p>
                <h2 class="summary-value" style="color: var(--danger); font-size: 1.25rem;">{{ number_format($this->totalExpenses, 0, ',', ' ') }}</h2>
            </div>
            <div class="summary-card" style="min-width: 140px; padding: 10px 16px;">
                <p class="summary-label">Monthly Savings</p>
                <h2 class="summary-value" style="color: var(--primary); font-size: 1.25rem;">{{ number_format($this->totalSavings, 0, ',', ' ') }}</h2>
            </div>
            <div class="summary-card accent" style="min-width: 140px; padding: 10px 16px;">
                <p class="summary-label" style="color: rgba(255,255,255,0.7);">Remaining</p>
                <h2 class="summary-value" style="font-size: 1.25rem;">{{ number_format($this->remaining, 0, ',', ' ') }}</h2>
            </div>
        </div>
    </div>

    <!-- Income, Savings, and Categories -->
    <div class="flex-cards" style="margin-bottom: var(--space-8);">

        <!-- Income Card -->
        <div class="card" style="flex: 1 1 400px;">
            <div class="card-header">
                <h3 style="font-weight: 900;">Income</h3>
                <button wire:click="addIncomeRow" class="eco-add-btn" title="Add income">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </button>
            </div>
            <div class="eco-grid-table">
                <div class="eco-grid-header income-grid">
                    <div style="width: 20px;"></div>
                    <div></div>
                    <div>Name</div>
                    <div>Recipient</div>
                    <div style="text-align: right;">Amount</div>
                    <div></div>
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
                    <div class="eco-grid-row income-grid" wire:key="income-{{ $income->id }}">
                        <div class="eco-drag-handle" style="cursor: grab; color: var(--slate-300); display: flex; align-items: center; justify-content: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                        </div>
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--success); justify-self: center;" class="desktop-only"></div>
                        <div class="eco-mobile-stack">
                            <div class="eco-field">
                                <span class="mobile-label">Name</span>
                                <input type="text" class="eco-inline-input" value="{{ $income->name }}" placeholder="Income name"
                                    wire:change="updateIncome({{ $income->id }}, 'name', $event.target.value)">
                            </div>
                            <div class="eco-field">
                                <span class="mobile-label">Recipient</span>
                                <select class="eco-inline-select" style="width: 100%;"
                                    wire:change="updateIncome({{ $income->id }}, 'recipient_id', $event.target.value)">
                                    <option value="" @if(!$income->recipient_id) selected @endif>Recipient</option>
                                    @foreach($this->users as $user)
                                        <option value="{{ $user->id }}" @if($income->recipient_id == $user->id) selected @endif>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="eco-field">
                            <span class="mobile-label">Amount</span>
                            <input type="number" class="eco-inline-input eco-inline-amount" value="{{ $income->amount }}" placeholder="0" style="width: 100%;"
                                wire:change="updateIncome({{ $income->id }}, 'amount', $event.target.value)">
                        </div>
                        <div style="display: flex; justify-content: flex-end;">
                            <button wire:click="deleteIncome({{ $income->id }})" class="eco-delete-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; color: var(--slate-400); padding: 20px; font-size: 13px;">No income recorded.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Monthly Savings Card -->
        <div class="card" style="flex: 1 1 400px;">
            <div class="card-header">
                <h3 style="font-weight: 900;">Monthly Savings</h3>
                <button wire:click="addSavingRow" class="eco-add-btn" title="Add saving">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </button>
            </div>
            <div class="eco-grid-table">
                <div class="eco-grid-header savings-grid">
                    <div style="width: 20px;"></div>
                    <div>Purpose</div>
                    <div>Bank/App</div>
                    <div>Saver</div>
                    <div style="text-align: right;">Amount</div>
                    <div></div>
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
                    <div class="eco-grid-row savings-grid" wire:key="saving-{{ $saving->id }}">
                        <div class="eco-drag-handle" style="cursor: grab; color: var(--slate-300); display: flex; align-items: center; justify-content: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                        </div>
                        <div class="eco-mobile-stack">
                            <div class="eco-field">
                                <span class="mobile-label">Purpose</span>
                                <input type="text" class="eco-inline-input" value="{{ $saving->name }}" placeholder="Purpose"
                                    wire:change="updateSaving({{ $saving->id }}, 'name', $event.target.value)">
                            </div>
                            <div class="eco-field">
                                <span class="mobile-label">Bank/App</span>
                                <input type="text" class="eco-inline-input" value="{{ $saving->location }}" placeholder="Bank/App"
                                    wire:change="updateSaving({{ $saving->id }}, 'location', $event.target.value)">
                            </div>
                        </div>
                        <div class="eco-mobile-stack">
                            <div class="eco-field">
                                <span class="mobile-label">Saver</span>
                                <select class="eco-inline-select" style="width: 100%;"
                                    wire:change="updateSaving({{ $saving->id }}, 'saver_id', $event.target.value)">
                                    <option value="" @if(!$saving->saver_id) selected @endif>Saver</option>
                                    @foreach($this->users as $user)
                                        <option value="{{ $user->id }}" @if($saving->saver_id == $user->id) selected @endif>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="eco-field">
                                <span class="mobile-label">Amount</span>
                                <input type="number" class="eco-inline-input eco-inline-amount" value="{{ $saving->amount }}" placeholder="0" style="width: 100%;"
                                    wire:change="updateSaving({{ $saving->id }}, 'amount', $event.target.value)">
                            </div>
                        </div>
                        <div style="display: flex; justify-content: flex-end;">
                            <button wire:click="deleteSaving({{ $saving->id }})" class="eco-delete-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; color: var(--slate-400); padding: 20px; font-size: 13px;">No savings recorded.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Categories Card -->
        <div class="card" style="flex: 0 0 360px;">
            <div class="card-header">
                <div>
                    <h3 style="font-weight: 900;">Categories</h3>
                </div>
                <button wire:click="addExpenseCategoryRow" class="eco-add-btn" title="Add category">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </button>
            </div>
            <div class="eco-grid-table">
                <div class="eco-grid-header categories-grid">
                    <div style="width: 20px;"></div>
                    <div></div>
                    <div>Name</div>
                    <div style="text-align: right;">Total Sum</div>
                    <div></div>
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
                        <div class="eco-drag-handle" style="cursor: grab; color: var(--slate-300); display: flex; align-items: center; justify-content: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                        </div>
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary); justify-self: center;"></div>
                        <input type="text" class="eco-inline-input" value="{{ $category->name }}" placeholder="Category name"
                            wire:change="updateExpenseCategory({{ $category->id }}, 'name', $event.target.value)">
                        <div style="font-size: 12px; font-weight: 900; color: var(--text-main); text-align: right; font-family: var(--font-heading);">
                            {{ number_format($catTotal, 0, ',', ' ') }} kr
                        </div>
                        <div style="display: flex; justify-content: flex-end;">
                            <button wire:click="deleteExpenseCategory({{ $category->id }})" class="eco-delete-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; color: var(--slate-400); padding: 20px; font-size: 13px;">No categories yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- Expenses — full width -->
    <div class="card">
        <div class="card-header" style="border-radius: 24px 24px 0 0;">
            <div>
                <h3 style="font-weight: 900;">Expenses</h3>
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <p style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--slate-400); margin: 0;">Recurring & one-time</p>
                    <div style="display: flex; gap: 8px; align-items: center; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                        <span style="display: flex; gap: 4px; align-items: center; color: var(--primary);">
                            <span style="width: 5px; height: 5px; border-radius: 50%; background: var(--primary);"></span>
                            Split: Divide equally
                        </span>
                        <span style="display: flex; gap: 4px; align-items: center; color: var(--warning);">
                            <span style="width: 5px; height: 5px; border-radius: 50%; background: var(--warning);"></span>
                            Delay: Pay later (not 25th)
                        </span>
                    </div>
                </div>
            </div>
            <button wire:click="addExpenseRow" class="eco-add-btn" title="Add expense">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            </button>
        </div>

        <div class="eco-grid-table">
            <div class="eco-grid-header expenses-grid">
                <div style="width: 20px;"></div>
                <div>Name</div>
                <div>Category</div>
                <div>Payer(s)</div>
                <div>Handling</div>
                <div>Options</div>
                <div style="text-align: right;">Amount</div>
                <div></div>
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
                            <span class="mobile-label">Name</span>
                            <input type="text" class="eco-inline-input" value="{{ $expense->name }}" placeholder="Expense name"
                                wire:change="updateExpense({{ $expense->id }}, 'name', $event.target.value)">
                        </div>
                        <div class="eco-field">
                            <span class="mobile-label">Category</span>
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
                        <span class="mobile-label">Payer(s)</span>
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
                                    <span class="eco-payer-placeholder">Select...</span>
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
                            <span class="mobile-label">Handling</span>
                            <select class="eco-inline-select" style="width: 100%;"
                                wire:change="updateExpense({{ $expense->id }}, 'handling', $event.target.value)">
                                <option value="Autogiro" @if($expense->handling === 'Autogiro') selected @endif>Autogiro</option>
                                <option value="Manuellt" @if($expense->handling === 'Manuellt') selected @endif>Manuellt</option>
                                <option value="E-faktura" @if($expense->handling === 'E-faktura') selected @endif>E-faktura</option>
                                <option value="Swish" @if($expense->handling === 'Swish') selected @endif>Swish</option>
                            </select>
                        </div>
                        <div class="eco-field">
                            <span class="mobile-label">Options</span>
                            <div style="display: flex; gap: 8px; align-items: center; height: 100%;">
                                <label class="eco-toggle-label" title="Split cost">
                                    <input type="checkbox" @if($expense->split) checked @endif
                                        wire:change="updateExpense({{ $expense->id }}, 'split', $event.target.checked ? 1 : 0)">
                                    <span class="eco-toggle-text">Split</span>
                                </label>
                                <label class="eco-toggle-label" title="Delayed payment">
                                    <input type="checkbox" @if($expense->delayed) checked @endif
                                        wire:change="updateExpense({{ $expense->id }}, 'delayed', $event.target.checked ? 1 : 0)">
                                    <span class="eco-toggle-text eco-toggle-warn">Delay</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="eco-field" style="text-align: right;">
                        <span class="mobile-label">Amount</span>
                        <input type="number" class="eco-inline-input eco-inline-amount" value="{{ $expense->amount }}" placeholder="0" style="width: 100%;"
                            wire:change="updateExpense({{ $expense->id }}, 'amount', $event.target.value)">
                    </div>
                    <div style="text-align: right; display: flex; justify-content: flex-end; align-items: center; height: 100%;" class="desktop-only">
                        <button wire:click="deleteExpense({{ $expense->id }})" class="eco-delete-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        </button>
                    </div>
                </div>
                @empty
                <div style="text-align: center; color: var(--slate-400); padding: 40px; font-size: 13px;">No expenses recorded. Click + to add one.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
