<?php

namespace App\Livewire\Economy;

use App\Models\SavingsSnapshot;
use App\Models\User;
use App\Services\EconomySnapshotService;
use Livewire\Component;

class SavingsHistory extends Component
{
    public $selectedSnapshotId;

    public function mount()
    {
        $lastSnapshot = SavingsSnapshot::orderBy('id', 'desc')->first();
        if ($lastSnapshot) {
            $this->selectedSnapshotId = $lastSnapshot->id;
        }
    }

    public function selectSnapshot($id)
    {
        $this->selectedSnapshotId = $id;
    }

    public function triggerManualSnapshot(EconomySnapshotService $service)
    {
        $service->captureSavingsSnapshot();
        session()->flash('message', 'Savings snapshot captured successfully.');
        
        $newSnapshot = SavingsSnapshot::latest()->first();
        if ($newSnapshot) {
            $this->selectedSnapshotId = $newSnapshot->id;
        }
    }

    public function deleteSnapshot($id)
    {
        SavingsSnapshot::destroy($id);
        
        if ($this->selectedSnapshotId == $id) {
            $this->selectedSnapshotId = SavingsSnapshot::latest()->first()?->id;
        }
        
        session()->flash('message', 'Snapshot deleted.');
    }

    public function getSnapshotsProperty()
    {
        return SavingsSnapshot::latest()->get();
    }

    public function getSelectedSnapshotProperty()
    {
        return SavingsSnapshot::find($this->selectedSnapshotId);
    }

    public function getUsersProperty()
    {
        return User::all();
    }

    public function render()
    {
        return view('livewire.economy.savings-history')
            ->layout('layouts.app');
    }
}
