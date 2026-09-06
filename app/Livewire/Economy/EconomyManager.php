<?php

namespace App\Livewire\Economy;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\Saving;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EconomyManager extends Component
{
    public bool $isEditing = false;

    public function toggleEditMode()
    {
        $this->isEditing = ! $this->isEditing;
    }

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
            'one_time_fee' => false,
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
        $rules = [
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0|max:9999999999.99',
            'recipient_id' => 'nullable|integer|exists:users,id',
        ];
        if (! isset($rules[$field]) || ! $this->isValid($value, $rules[$field])) {
            return;
        }

        $income = Income::find($id);
        if (! $income) {
            return;
        }

        $income->update([$field => is_string($value) ? trim($value) : $value]);
    }

    public function updateExpense($id, $field, $value)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0|max:9999999999.99',
            'category' => 'nullable|string|max:255',
            'handling' => 'nullable|string|max:255',
            'split' => 'required|boolean',
            'delayed' => 'required|boolean',
            'one_time_fee' => 'required|boolean',
        ];
        if (! isset($rules[$field]) || ! $this->isValid($value, $rules[$field])) {
            return;
        }

        $expense = Expense::find($id);
        if (! $expense) {
            return;
        }

        if (in_array($field, ['split', 'delayed', 'one_time_fee'])) {
            $value = (bool) $value;
        }

        $expense->update([$field => is_string($value) ? trim($value) : $value]);
    }

    public function reorder($type, $orderedIds)
    {
        if (! is_array($orderedIds)) {
            return;
        }
        $orderedIds = array_values(array_unique(array_filter($orderedIds, 'is_numeric')));

        $model = match ($type) {
            'income' => Income::class,
            'expense' => Expense::class,
            'saving' => Saving::class,
            'category' => ExpenseCategory::class,
            default => null
        };

        if (! $model) {
            return;
        }

        foreach ($orderedIds as $index => $id) {
            $record = $model::find($id);
            if ($record && $record->sort_order != $index) {
                $record->update(['sort_order' => $index]);
            }
        }
    }

    public function addExpenseCategoryRow()
    {
        $baseName = __('New category');
        $name = $baseName;
        $suffix = 2;

        while (ExpenseCategory::where('name', $name)->exists()) {
            $name = "{$baseName} {$suffix}";
            $suffix++;
        }

        ExpenseCategory::create([
            'name' => $name,
            'sort_order' => ExpenseCategory::max('sort_order') + 1,
        ]);
    }

    public function updateExpenseCategory($id, $field, $value)
    {
        $allowed = ['name'];
        if (! in_array($field, $allowed)) {
            return;
        }

        if ($field === 'name' && (! is_string($value) || trim($value) === '' || mb_strlen(trim($value)) > 255)) {
            return;
        }

        $category = ExpenseCategory::find($id);
        if (! $category) {
            return;
        }

        $errorKey = 'categoryNames.'.$category->id;
        $this->resetValidation($errorKey);
        $validator = Validator::make(
            ['name' => trim($value)],
            ['name' => [Rule::unique('expense_categories', 'name')->ignore($category->id)]]
        );
        if ($validator->fails()) {
            $this->addError($errorKey, __('This category name is already in use.'));

            return;
        }

        DB::transaction(function () use ($category, $value) {
            $oldName = $category->name;
            $category->update(['name' => trim($value)]);
            Expense::where('category', $oldName)->update(['category' => $category->name]);
        });
    }

    public function deleteExpenseCategory($id)
    {
        ExpenseCategory::find($id)?->delete();
    }

    public function toggleExpensePayer($id, $userId)
    {
        if (! User::whereKey($userId)->exists()) {
            return;
        }

        $expense = Expense::find($id);
        if (! $expense) {
            return;
        }

        $payerIds = $expense->payer_ids ?? [];

        if (in_array((int) $userId, $payerIds)) {
            $payerIds = array_values(array_diff($payerIds, [(int) $userId]));
        } else {
            $payerIds[] = (int) $userId;
        }

        $expense->update(['payer_ids' => $payerIds]);
    }

    // --- Delete Methods ---

    public function updateSaving($id, $field, $value)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0|max:9999999999.99',
            'saver_id' => 'nullable|integer|exists:users,id',
            'location' => 'nullable|string|max:255',
        ];
        if (! isset($rules[$field]) || ! $this->isValid($value, $rules[$field])) {
            return;
        }

        $saving = Saving::find($id);
        if (! $saving) {
            return;
        }

        $saving->update([$field => is_string($value) ? trim($value) : $value]);
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

    private function isValid($value, string $rule): bool
    {
        return ! Validator::make(['value' => $value], ['value' => $rule])->fails();
    }
}
