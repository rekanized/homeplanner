<?php

namespace App\Livewire\Economy;

use App\Models\EconomySnapshot;
use App\Services\EconomySnapshotService;
use App\Support\HistoryChartData;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MonthlyHistory extends Component
{
    public $selectedSnapshotId = null;

    public function mount()
    {
        $this->selectedSnapshotId = EconomySnapshot::orderByDesc('id')->value('id');
    }

    #[Computed]
    public function snapshots()
    {
        return EconomySnapshot::orderBy('year', 'desc')->orderBy('month', 'desc')->orderByDesc('id')
            ->get(['id', 'year', 'month', 'created_at', 'total_income', 'total_expenses', 'total_savings']);
    }

    public function selectSnapshot($id)
    {
        $this->selectedSnapshotId = $id;
    }

    #[Computed]
    public function chartData(): array
    {
        return HistoryChartData::monthly($this->snapshots);
    }

    public function triggerManualSnapshot(EconomySnapshotService $service)
    {
        $snapshot = $service->capture();
        $this->selectedSnapshotId = $snapshot->id;
        session()->flash('message', __('Manual snapshot captured successfully.'));
    }

    public function deleteSnapshot($id)
    {
        EconomySnapshot::find($id)?->delete();
        if ($this->selectedSnapshotId == $id) {
            $this->selectedSnapshotId = null;
        }
    }

    #[Computed]
    public function selectedSnapshot()
    {
        return $this->selectedSnapshotId ? EconomySnapshot::find($this->selectedSnapshotId) : null;
    }

    public function render()
    {
        return view('livewire.economy.monthly-history');
    }
}
