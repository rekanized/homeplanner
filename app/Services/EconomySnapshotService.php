<?php

namespace App\Services;

use App\Models\Income;
use App\Models\Expense;
use App\Models\Saving;
use App\Models\SavingsBalance;
use App\Models\ExpenseCategory;
use App\Models\EconomySnapshot;
use Illuminate\Support\Facades\DB;

class EconomySnapshotService
{
    public function capture()
    {
        return DB::transaction(function () {
            $month = now()->month;
            $year = now()->year;

            // Optional: Prevent duplicate manual snapshots if desired, 
            // but usually manual means "I want one now". 
            // For simplicity, we'll allow multiple snapshots but they'll have different timestamps.
            
            $data = [
                'incomes' => Income::orderBy('sort_order')->get()->toArray(),
                'expenses' => Expense::orderBy('sort_order')->get()->toArray(),
                'savings' => Saving::orderBy('sort_order')->get()->toArray(),
                'savings_balances' => SavingsBalance::orderBy('sort_order')->get()->toArray(),
                'categories' => ExpenseCategory::orderBy('sort_order')->get()->toArray(),
            ];

            $totalIncome = Income::sum('amount');
            $totalExpenses = Expense::sum('amount');
            $totalMonthlySavings = Saving::sum('amount');
            $totalAccumulatedSavings = SavingsBalance::sum('amount');

            return EconomySnapshot::create([
                'month' => $month,
                'year' => $year,
                'snapshot_data' => $data,
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'total_savings' => $totalMonthlySavings, // Keeping this as monthly for backward compat/summary
            ]);
        });
    }
}
