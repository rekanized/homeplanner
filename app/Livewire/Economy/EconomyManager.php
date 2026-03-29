<?php

namespace App\Livewire\Economy;

use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Saving;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Computed;

class EconomyManager extends Component
{
    #[Computed]
    public function users()
    {
        return User::all();
    }

    #[Computed]
    public function incomes()
    {
        return Income::orderBy('sort_order')->get();
    }

    #[Computed]
    public function expenses()
    {
        return Expense::orderBy('sort_order')->get();
    }

    #[Computed]
    public function expenseCategories()
    {
        return ExpenseCategory::orderBy('sort_order')->get();
    }

    #[Computed]
    public function savings()
    {
        return Saving::orderBy('sort_order')->get();
    }

    #[Computed]
    public function totalIncome()
    {
        return $this->incomes()->sum('amount');
    }

    #[Computed]
    public function totalExpenses()
    {
        return $this->expenses()->sum('amount');
    }

    #[Computed]
    public function totalDirectExpenses()
    {
        return $this->expenses()->where('delayed', false)->sum('amount');
    }

    #[Computed]
    public function totalDelayedExpenses()
    {
        return $this->expenses()->where('delayed', true)->sum('amount');
    }

    #[Computed]
    public function totalSavings()
    {
        return $this->savings()->sum('amount');
    }

    #[Computed]
    public function remaining()
    {
        return $this->totalIncome() - $this->totalExpenses() - $this->totalSavings();
    }

    // --- Add Row Methods ---

    public function addIncomeRow()
    {
        Income::create([
            'name' => '',
            'amount' => 0,
            'recipient_id' => null,
            'sort_order' => Income::max('sort_order') + 1,
        ]);
    }

    public function addExpenseRow()
    {
        Expense::create([
            'name' => '',
            'amount' => 0,
            'category' => '',
            'payer_ids' => [],
            'handling' => __('Autogiro'),
            'split' => false,
            'delayed' => false,
            'sort_order' => Expense::max('sort_order') + 1,
        ]);
    }

    public function addSavingRow()
    {
        Saving::create([
            'name' => '',
            'amount' => 0,
            'saver_id' => null,
            'location' => '',
            'sort_order' => Saving::max('sort_order') + 1,
        ]);
    }

    // --- Inline Update Methods ---

    public function updateIncome($id, $field, $value)
    {
        $allowed = ['name', 'amount', 'recipient_id'];
        if (!in_array($field, $allowed)) return;

        $income = Income::find($id);
        if (!$income) return;

        $income->update([$field => $value]);
    }

    public function updateExpense($id, $field, $value)
    {
        $allowed = ['name', 'amount', 'category', 'handling', 'split', 'delayed'];
        if (!in_array($field, $allowed)) return;

        // Basic validation
        if ($field === 'amount' && !is_numeric($value)) return;
        if ($field === 'name' && empty(trim($value))) return;

        $expense = Expense::find($id);
        if (!$expense) return;

        if (in_array($field, ['split', 'delayed'])) {
            $value = (bool) $value;
        }

        $expense->update([$field => $value]);
    }

    public function reorder($type, $orderedIds)
    {
        $model = match($type) {
            'income' => Income::class,
            'expense' => Expense::class,
            'saving' => Saving::class,
            'category' => ExpenseCategory::class,
            default => null
        };

        if (!$model) return;

        foreach ($orderedIds as $index => $id) {
            $record = $model::find($id);
            if ($record && $record->sort_order != $index) {
                $record->update(['sort_order' => $index]);
            }
        }
    }

    public function addExpenseCategoryRow()
    {
        ExpenseCategory::create([
            'name' => '',
            'sort_order' => ExpenseCategory::max('sort_order') + 1,
        ]);
    }

    public function updateExpenseCategory($id, $field, $value)
    {
        $allowed = ['name'];
        if (!in_array($field, $allowed)) return;

        if ($field === 'name' && empty(trim($value))) return;

        $category = ExpenseCategory::find($id);
        if (!$category) return;

        $category->update([$field => $value]);
    }

    public function deleteExpenseCategory($id)
    {
        ExpenseCategory::find($id)?->delete();
    }

    public function toggleExpensePayer($id, $userId)
    {
        $expense = Expense::find($id);
        if (!$expense) return;

        $payerIds = $expense->payer_ids ?? [];

        if (in_array((int)$userId, $payerIds)) {
            $payerIds = array_values(array_diff($payerIds, [(int)$userId]));
        } else {
            $payerIds[] = (int)$userId;
        }

        $expense->update(['payer_ids' => $payerIds]);
    }

    // --- Delete Methods ---

    public function updateSaving($id, $field, $value)
    {
        $allowed = ['name', 'amount', 'saver_id', 'location'];
        if (!in_array($field, $allowed)) return;

        $saving = Saving::find($id);
        if (!$saving) return;

        $saving->update([$field => $value]);
    }


    public function deleteIncome($id)
    {
        Income::find($id)?->delete();
    }

    public function deleteExpense($id)
    {
        Expense::find($id)?->delete();
    }

    public function deleteSaving($id)
    {
        Saving::find($id)?->delete();
    }

    public function render()
    {
        return view('livewire.economy.economy-manager');
    }
}
