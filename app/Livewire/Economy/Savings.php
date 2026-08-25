<?php

namespace App\Livewire\Economy;

use App\Models\SavingsBalance;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Savings extends Component
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
        $rules = [
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0|max:9999999999.99',
            'saver_id' => 'nullable|integer|exists:users,id',
            'location' => 'nullable|string|max:255',
        ];
        if (! isset($rules[$field]) || Validator::make(['value' => $value], ['value' => $rules[$field]])->fails()) {
            return;
        }

        $saving = SavingsBalance::find($id);
        if (! $saving) {
            return;
        }

        $saving->update([$field => is_string($value) ? trim($value) : $value]);
    }

    public function deleteSaving($id)
    {
        SavingsBalance::find($id)?->delete();
    }

    public function reorder($type, $orderedIds)
    {
        if ($type !== 'saving' || ! is_array($orderedIds)) {
            return;
        }

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
