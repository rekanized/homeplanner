<?php

namespace App\Livewire\Economy;

use App\Models\SavingsBalance;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Savings extends Component
{
    #[Computed]
    public function users()
    {
        return User::all();
    }

    #[Computed]
    public function savings()
    {
        return SavingsBalance::orderBy('sort_order')->get();
    }

    #[Computed]
    public function totalSavings()
    {
        return $this->savings()->sum('amount');
    }

    public function addSavingRow()
    {
        SavingsBalance::create([
            'name' => '',
            'amount' => 0,
            'saver_id' => null,
            'location' => '',
            'sort_order' => SavingsBalance::max('sort_order') + 1,
        ]);
    }

    public function updateSaving($id, $field, $value)
    {
        $allowed = ['name', 'amount', 'saver_id', 'location'];
        if (!in_array($field, $allowed)) return;

        $saving = SavingsBalance::find($id);
        if (!$saving) return;

        $saving->update([$field => $value]);
    }

    public function deleteSaving($id)
    {
        SavingsBalance::find($id)?->delete();
    }

    public function reorder($type, $orderedIds)
    {
        if ($type !== 'saving') return;

        foreach ($orderedIds as $index => $id) {
            $record = SavingsBalance::find($id);
            if ($record && $record->sort_order != $index) {
                $record->update(['sort_order' => $index]);
            }
        }
    }

    public function render()
    {
        return view('livewire.economy.savings');
    }
}
