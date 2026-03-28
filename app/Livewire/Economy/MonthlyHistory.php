<?php

namespace App\Livewire\Economy;

use Livewire\Component;

use App\Models\EconomySnapshot;
use App\Services\EconomySnapshotService;
use Livewire\Attributes\Computed;

class MonthlyHistory extends Component
{
    public $selectedSnapshotId = null;

    #[Computed]
    public function snapshots()
    {
        return EconomySnapshot::orderBy('year', 'desc')->orderBy('month', 'desc')->get();
    }

    public function selectSnapshot($id)
    {
        $this->selectedSnapshotId = $id;
    }

    public function triggerManualSnapshot(EconomySnapshotService $service)
    {
        $snapshot = $service->capture();
        $this->selectedSnapshotId = $snapshot->id;
        session()->flash('message', 'Manual snapshot captured successfully.');
    }

    public function deleteSnapshot($id)
    {
        EconomySnapshot::find($id)?->delete();
        if ($this->selectedSnapshotId == $id) {
            $this->selectedSnapshotId = null;
        }
    }

    public function render()
    {
        $snapshot = $this->selectedSnapshotId ? EconomySnapshot::find($this->selectedSnapshotId) : null;

        return view('livewire.economy.monthly-history', [
            'selectedSnapshot' => $snapshot,
        ]);
    }
}
