<div>

    <!-- Month Picker & Header -->
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1 style="font-size: 2.25rem; font-weight: 900; margin-bottom: 4px;">Monthly Economy</h1>
            <p style="color: var(--text-muted); font-weight: 600;">Track and manage your household finances.</p>
        </div>
        <div style="display: flex; align-items: center; background: var(--bg-card); padding: 8px; border-radius: 16px; border: 1px solid var(--border-color); shadow: var(--shadow);">
            <select wire:model.live="month" class="form-select" style="background: transparent; border: none; font-weight: 900; width: auto; color: var(--primary);">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                @endforeach
            </select>
            <div style="width: 1px; height: 16px; background: var(--border-color); margin: 0 8px;"></div>
            <select wire:model.live="year" class="form-select" style="background: transparent; border: none; font-weight: 900; width: auto; color: var(--primary);">
                @foreach(range(2024, 2030) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card">
            <p class="summary-label">Total Income</p>
            <h2 class="summary-value" style="color: var(--success);">{{ number_format($this->totalIncome, 0, ',', ' ') }} <span style="font-size: 11px; opacity: 0.5;">KR</span></h2>
        </div>
        <div class="summary-card">
            <p class="summary-label">Total Expenses</p>
            <h2 class="summary-value" style="color: var(--danger);">{{ number_format($this->totalExpenses, 0, ',', ' ') }} <span style="font-size: 11px; opacity: 0.5;">KR</span></h2>
        </div>
        <div class="summary-card">
            <p class="summary-label">Total Savings</p>
            <h2 class="summary-value" style="color: var(--primary);">{{ number_format($this->totalSavings, 0, ',', ' ') }} <span style="font-size: 11px; opacity: 0.5;">KR</span></h2>
        </div>
        <div class="summary-card accent">
            <p class="summary-label" style="color: rgba(255,255,255,0.7);">Remaining</p>
            <h2 class="summary-value">{{ number_format($this->remaining, 0, ',', ' ') }} <span style="font-size: 11px; opacity: 0.5;">KR</span></h2>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: var(--space-8);">
        <!-- Expenses Card -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="font-weight: 900;">Expenses</h3>
                    <p style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--slate-400); margin: 0;">Monthly billing</p>
                </div>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--danger-soft); color: var(--danger); display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="m17 5-5-3-5 3"/><path d="m17 19-5 3-5-3"/></svg>
                </div>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Expense</th>
                            <th>Details</th>
                            <th style="text-align: right;">Amount</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->expenses as $expense)
                        <tr>
                            <td>
                                <div style="font-weight: 800; font-size: 14px;">{{ $expense->name }}</div>
                                <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--slate-400);">{{ $expense->category }}</div>
                            </td>
                            <td>
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    <span class="badge badge-soft">{{ $expense->payer }}</span>
                                    <span class="badge badge-soft">{{ $expense->handling }}</span>
                                    @if($expense->split) <span class="badge" style="background: var(--success-soft); color: var(--success);">Split</span> @endif
                                    @if($expense->delayed) <span class="badge" style="background: var(--warning-soft); color: var(--warning);">Delayed</span> @endif
                                </div>
                            </td>
                            <td style="text-align: right; font-weight: 900;">{{ number_format($expense->amount, 0, ',', ' ') }} kr</td>
                            <td style="text-align: right;">
                                <button wire:click="deleteExpense({{ $expense->id }})" style="background: transparent; border: none; color: var(--slate-300); cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--slate-300)'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--slate-400); padding: 40px;">No expenses for this month.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="padding: 24px; background: var(--bg-main); border-top: 1px solid var(--border-color);">
                <form wire:submit="addExpense" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Expense Name</label>
                        <input type="text" wire:model="expenseName" class="form-input" placeholder="e.g. Rent">
                        @error('expenseName') <span style="font-size: 10px; color: var(--danger); font-weight: 800;">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount (KR)</label>
                        <input type="number" wire:model="expenseAmount" class="form-input" placeholder="0">
                        @error('expenseAmount') <span style="font-size: 10px; color: var(--danger); font-weight: 800;">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select wire:model="expenseCategory" class="form-select">
                            <option value="">Select Category</option>
                            <option value="Boende">Boende</option>
                            <option value="Bil">Bil</option>
                            <option value="Försäkring">Försäkring</option>
                            <option value="Mat">Mat</option>
                            <option value="Annat">Annat</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payer</label>
                        <select wire:model="expensePayer" class="form-select">
                            <option value="">Select Payer</option>
                            @foreach($this->users as $user)
                                <option value="{{ $user->name }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="grid-column: span 2; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; gap: 16px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" wire:model="expenseSplit" style="width: 18px; height: 18px; accent-color: var(--primary);">
                                <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--slate-500);">Split Cost</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" wire:model="expenseDelayed" style="width: 18px; height: 18px; accent-color: var(--warning);">
                                <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--slate-500);">Delayed</span>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Expense</button>
                    </div>
                </form>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: var(--space-8);">
            <!-- Income Card -->
            <div class="card">
                <div class="card-header">
                    <h3 style="font-weight: 900;">Income</h3>
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--success-soft); color: var(--success); display: flex; align-items: center; justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="m17 5-5-3-5 3"/></svg>
                    </div>
                </div>
                <div class="card-body" style="display: flex; flex-direction: column; gap: 12px;">
                    @forelse($this->incomes as $income)
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border-radius: 16px; background: var(--bg-main);">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--success);"></div>
                            <div>
                                <div style="font-weight: 800; font-size: 14px;">{{ $income->name }}</div>
                                <div style="font-size: 10px; font-weight: 800; color: var(--slate-400); text-transform: uppercase;">{{ $income->recipient }}</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <span style="font-weight: 900;">{{ number_format($income->amount, 0, ',', ' ') }} kr</span>
                            <button wire:click="deleteIncome({{ $income->id }})" style="background: transparent; border: none; color: var(--slate-300); cursor: pointer;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                    @empty
                    <p style="text-align: center; color: var(--slate-400); font-size: 13px;">No income recorded.</p>
                    @endforelse

                    <form wire:submit="addIncome" style="margin-top: 12px; display: grid; grid-template-columns: 1fr 1fr 100px auto; gap: 8px; align-items: end;">
                        <input type="text" wire:model="incomeName" class="form-input" placeholder="Name">
                        <select wire:model="incomeRecipient" class="form-select">
                            <option value="">Recipient</option>
                            @foreach($this->users as $user)
                                <option value="{{ $user->name }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" wire:model="incomeAmount" class="form-input" placeholder="KR" style="text-align: right;">
                        <button type="submit" class="btn btn-success" style="padding: 10px 16px;">Add</button>
                    </form>
                </div>
            </div>

            <!-- Savings Card -->
            <div class="card">
                <div class="card-header">
                    <h3 style="font-weight: 900;">Savings</h3>
                </div>
                <div class="card-body" style="display: flex; flex-direction: column; gap: 12px;">
                    @forelse($this->savings as $saving)
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border-radius: 16px; background: var(--bg-main);">
                        <div>
                            <div style="font-weight: 800; font-size: 14px;">{{ $saving->name }}</div>
                            <div style="font-size: 10px; font-weight: 800; color: var(--slate-400); text-transform: uppercase;">{{ $saving->location }} • {{ $saving->saver }}</div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <span style="font-weight: 900;">{{ number_format($saving->amount, 0, ',', ' ') }} kr</span>
                            <button wire:click="deleteSaving({{ $saving->id }})" style="background: transparent; border: none; color: var(--slate-300); cursor: pointer;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                    @empty
                    <p style="text-align: center; color: var(--slate-400); font-size: 13px;">No savings recorded.</p>
                    @endforelse

                    <form wire:submit="addSaving" style="margin-top: 12px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <input type="text" wire:model="savingName" class="form-input" placeholder="Purpose">
                        <input type="text" wire:model="savingLocation" class="form-input" placeholder="Bank/App">
                        <select wire:model="savingSaver" class="form-select">
                            <option value="">Saver</option>
                            @foreach($this->users as $user)
                                <option value="{{ $user->name }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <div style="display: flex; gap: 8px;">
                            <input type="number" wire:model="savingAmount" class="form-input" placeholder="Amount" style="flex: 1;">
                            <button type="submit" class="btn btn-primary">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
