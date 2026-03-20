<?php

namespace App\Livewire\Economy;

use App\Models\Income;
use App\Models\Expense;
use App\Models\Saving;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Dashboard extends Component
{
    public $month;
    public $year;

    // Form fields
    public $incomeName = '';
    public $incomeAmount = '';
    public $incomeRecipient = '';

    public $expenseName = '';
    public $expenseAmount = '';
    public $expenseCategory = '';
    public $expensePayer = '';
    public $expenseHandling = 'Autogiro';
    public $expenseSplit = false;
    public $expenseDelayed = false;

    public $savingName = '';
    public $savingAmount = '';
    public $savingSaver = '';
    public $savingLocation = '';
    public $savingHandling = 'Manuellt';

    public function mount()
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    #[Computed]
    public function users()
    {
        return User::all();
    }

    #[Computed]
    public function incomes()
    {
        return Income::where('month', $this->month)->where('year', $this->year)->get();
    }

    #[Computed]
    public function expenses()
    {
        return Expense::where('month', $this->month)->where('year', $this->year)->get();
    }

    #[Computed]
    public function savings()
    {
        return Saving::where('month', $this->month)->where('year', $this->year)->get();
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
    public function totalSavings()
    {
        return $this->savings()->sum('amount');
    }

    #[Computed]
    public function remaining()
    {
        return $this->totalIncome() - $this->totalExpenses() - $this->totalSavings();
    }

    public function addIncome()
    {
        $this->validate([
            'incomeName' => 'required|min:2',
            'incomeAmount' => 'required|numeric|gt:0',
            'incomeRecipient' => 'required',
        ], [
            'incomeName.required' => 'Vad är det för inkomst?',
            'incomeAmount.required' => 'Hur mycket?',
            'incomeAmount.numeric' => 'Måste vara ett nummer.',
            'incomeRecipient.required' => 'Vem fick pengarna?',
        ]);

        Income::create([
            'name' => $this->incomeName,
            'amount' => $this->incomeAmount,
            'recipient' => $this->incomeRecipient,
            'month' => $this->month,
            'year' => $this->year,
        ]);

        $this->reset(['incomeName', 'incomeAmount', 'incomeRecipient']);
    }

    public function addExpense()
    {
        $this->validate([
            'expenseName' => 'required|min:2',
            'expenseAmount' => 'required|numeric|gt:0',
            'expenseCategory' => 'required',
            'expensePayer' => 'required',
            'expenseHandling' => 'required',
        ], [
            'expenseName.required' => 'Vad är det för utgift?',
            'expenseAmount.required' => 'Hur mycket kostade det?',
            'expenseCategory.required' => 'Välj en kategori.',
            'expensePayer.required' => 'Vem betalade?',
        ]);

        Expense::create([
            'name' => $this->expenseName,
            'amount' => $this->expenseAmount,
            'category' => $this->expenseCategory,
            'payer' => $this->expensePayer,
            'handling' => $this->expenseHandling,
            'split' => $this->expenseSplit,
            'delayed' => $this->expenseDelayed,
            'month' => $this->month,
            'year' => $this->year,
        ]);

        $this->reset(['expenseName', 'expenseAmount', 'expenseCategory', 'expensePayer', 'expenseHandling', 'expenseSplit', 'expenseDelayed']);
    }

    public function addSaving()
    {
        $this->validate([
            'savingName' => 'required|min:2',
            'savingAmount' => 'required|numeric|gt:0',
            'savingSaver' => 'required',
            'savingLocation' => 'required',
            'savingHandling' => 'required',
        ], [
            'savingName.required' => 'Vad sparar du till?',
            'savingAmount.required' => 'Hur mycket?',
            'savingSaver.required' => 'Vem sparar?',
            'savingLocation.required' => 'Vart sparas det?',
        ]);

        Saving::create([
            'name' => $this->savingName,
            'amount' => $this->savingAmount,
            'saver' => $this->savingSaver,
            'location' => $this->savingLocation,
            'handling' => $this->savingHandling,
            'month' => $this->month,
            'year' => $this->year,
        ]);

        $this->reset(['savingName', 'savingAmount', 'savingSaver', 'savingLocation', 'savingHandling']);
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
        return view('livewire.economy.dashboard');
    }
}
