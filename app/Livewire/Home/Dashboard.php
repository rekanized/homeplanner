<?php

namespace App\Livewire\Home;

use Livewire\Component;
use App\Models\Income;
use App\Models\Saving;
use App\Models\Expense;

class Dashboard extends Component
{
    public function render()
    {
        $totalIncome = Income::sum('amount');
        $totalSavings = Saving::sum('amount');
        $totalExpenses = Expense::sum('amount');

        return view('livewire.home.dashboard', [
            'totalIncome' => $totalIncome,
            'totalSavings' => $totalSavings,
            'totalExpenses' => $totalExpenses,
        ]);
    }
}
