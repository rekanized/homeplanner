<?php

namespace App\Livewire\Economy;

use App\Models\SavingsSnapshot;
use App\Models\User;
use App\Services\EconomySnapshotService;
use App\Support\HistoryChartData;
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
        $snapshot = $service->captureSavingsSnapshot();
        session()->flash('message', __('Savings snapshot captured successfully.'));
        
        $this->selectedSnapshotId = $snapshot->id;
    }

    public function deleteSnapshot($id)
    {
        SavingsSnapshot::destroy($id);
        
        if ($this->selectedSnapshotId == $id) {
            $this->selectedSnapshotId = SavingsSnapshot::latest()->orderByDesc('id')->first()?->id;
        }
        
        session()->flash('message', __('Snapshot deleted.'));
    }

    public function getSnapshotsProperty()
    {
        return SavingsSnapshot::latest()->orderByDesc('id')->get();
    }

    public function getChartDataProperty(): array
    {
        return HistoryChartData::savings($this->snapshots);
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
