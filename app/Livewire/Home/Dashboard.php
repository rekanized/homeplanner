<?php

namespace App\Livewire\Home;

use Livewire\Component;
use App\Models\Income;
use App\Models\Saving;
use App\Models\Expense;
use App\Models\ShoppingItem;
use App\Models\TodoItem;
use Carbon\Carbon;

class Dashboard extends Component
{
    public function render()
    {
        $totalIncome = Income::sum('amount');
        $totalSavings = Saving::sum('amount');
        $totalExpenses = Expense::sum('amount');
        
        $shoppingItemsCount = ShoppingItem::where('is_checked', false)->count();
        $todoItemsWaiting = TodoItem::where('is_done', false)->count();
        $todoItemsOverdue = TodoItem::where('is_done', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->count();

        // Chart Data: Completed Todos last 6 months
        $startDate = now()->subMonths(6)->startOfDay();
        $endDate = now()->endOfDay();
        
        $completedTodos = TodoItem::where('is_done', true)
            ->where('completed_at', '>=', $startDate)
            ->selectRaw('DATE(completed_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $chartPoints = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();
            $chartPoints[] = [
                'date' => $dateStr,
                'count' => $completedTodos[$dateStr] ?? 0
            ];
            $currentDate->addDay();
        }

        return view('livewire.home.dashboard', [
            'totalIncome' => $totalIncome,
            'totalSavings' => $totalSavings,
            'totalExpenses' => $totalExpenses,
            'shoppingItemsCount' => $shoppingItemsCount,
            'todoItemsWaiting' => $todoItemsWaiting,
            'todoItemsOverdue' => $todoItemsOverdue,
            'chartPoints' => $chartPoints,
            'totalCompleted' => array_sum(array_column($chartPoints, 'count')),
            'economyEnabled' => filter_var(\App\Models\Setting::get('module_economy_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'shoppingEnabled' => filter_var(\App\Models\Setting::get('module_shopping_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'todoEnabled' => filter_var(\App\Models\Setting::get('module_todo_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'kidsEnabled' => filter_var(\App\Models\Setting::get('module_kids_enabled', true), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}

