<?php

namespace Tests\Feature;

use App\Livewire\Economy\MonthlyHistory;
use App\Livewire\Economy\SavingsHistory;
use App\Models\EconomySnapshot;
use App\Models\SavingsBalance;
use App\Models\SavingsSnapshot;
use App\Models\User;
use App\Support\HistoryChartData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HistoryChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_chart_keeps_every_capture_in_date_order_and_calculates_remaining(): void
    {
        $later = $this->monthlySnapshot('2026-08-25 12:00:00', 3000.25, 1000, 500);
        $earlier = $this->monthlySnapshot('2026-08-01 12:00:00', 1000, 1100.50, 200);
        $sameTime = $this->monthlySnapshot('2026-08-25 12:00:00', 0, 0, 0);

        $data = HistoryChartData::monthly(EconomySnapshot::all());

        $this->assertSame([$earlier->id, $later->id, $sameTime->id], $data['snapshots']->modelKeys());
        $this->assertSame([1000.0, 3000.25, 0.0], $data['series'][0]['values']);
        $this->assertSame([-300.5, 1500.25, 0.0], $data['series'][3]['values']);

        Livewire::actingAs(User::factory()->create())->test(MonthlyHistory::class)
            ->assertSee('Monthly finances over time')
            ->assertSee('-300,50 kr')
            ->call('selectSnapshot', $earlier->id)
            ->assertSet('selectedSnapshotId', $earlier->id)
            ->call('deleteSnapshot', $earlier->id)
            ->assertDontSee('-300,50 kr');
    }

    public function test_savings_chart_shows_only_accumulated_totals_in_date_order(): void
    {
        $first = $this->savingsSnapshot('2026-06-01', 100, [
            ['id' => 5, 'name' => 'Old name', 'amount' => 100, 'location' => 'Bank'],
        ]);
        $middle = $this->savingsSnapshot('2026-07-01', 50, [
            ['id' => 6, 'name' => 'Other fund', 'amount' => 50],
        ]);
        $last = $this->savingsSnapshot('2026-08-01', 200.25, [
            ['id' => 5, 'name' => 'New name', 'amount' => 200.25, 'location' => 'Bank'],
            ['id' => 6, 'name' => 'Other fund', 'amount' => 0],
        ]);

        $data = HistoryChartData::savings(collect([$last, $first, $middle]));

        $this->assertCount(1, $data['series']);
        $this->assertSame([$first->id, $middle->id, $last->id], $data['snapshots']->pluck('id')->all());
        $this->assertSame(__('Total Accumulated'), $data['series'][0]['label']);
        $this->assertSame([100.0, 50.0, 200.25], $data['series'][0]['values']);
        Livewire::actingAs(User::factory()->create())->test(SavingsHistory::class)
            ->assertSee('Savings over time')
            ->assertDontSeeHtml('class="history-chart-key"');
    }

    public function test_savings_chart_supports_legacy_archived_balances_without_live_records(): void
    {
        $first = $this->savingsSnapshot('2026-07-01', 20, [
            ['name' => 'Archived fund', 'amount' => 20],
        ]);
        $last = $this->savingsSnapshot('2026-08-01', 30, [
            ['name' => 'Archived fund', 'amount' => 30],
        ]);

        $data = HistoryChartData::savings(collect([$last, $first]));

        $this->assertCount(1, $data['series']);
        $this->assertSame([20.0, 30.0], $data['series'][0]['values']);
    }

    public function test_both_charts_handle_empty_single_and_deleted_histories(): void
    {
        $this->actingAs(User::factory()->create());

        foreach ([MonthlyHistory::class, SavingsHistory::class] as $component) {
            $history = Livewire::test($component)
                ->assertSee('No history to graph yet.')
                ->call('triggerManualSnapshot')
                ->assertDontSee('No history to graph yet.')
                ->assertSee('Capture another snapshot to see the trend over time.');

            $id = $history->get('selectedSnapshotId');
            $history->call('deleteSnapshot', $id)->assertSee('No history to graph yet.');
        }
    }

    public function test_savings_chart_refreshes_amounts_when_a_new_snapshot_is_captured(): void
    {
        $balance = SavingsBalance::create(['name' => 'Emergency fund', 'amount' => 123.45, 'sort_order' => 1]);
        $history = Livewire::actingAs(User::factory()->create())->test(SavingsHistory::class)
            ->call('triggerManualSnapshot')
            ->assertSee('123,45 kr');

        $balance->update(['amount' => 678.90]);
        $history->call('triggerManualSnapshot')
            ->assertSee('123,45 kr')
            ->assertSee('678,90 kr')
            ->assertDontSee('Capture another snapshot to see the trend over time.');

        $history->call('deleteSnapshot', $history->get('selectedSnapshotId'))
            ->assertDontSee('678,90 kr')
            ->assertSee('123,45 kr');
    }

    private function monthlySnapshot(string $date, float $income, float $expenses, float $savings): EconomySnapshot
    {
        $snapshot = new EconomySnapshot([
            'year' => 2026, 'month' => 8,
            'total_income' => $income, 'total_expenses' => $expenses, 'total_savings' => $savings,
            'snapshot_data' => ['incomes' => [], 'expenses' => [], 'savings' => []],
        ]);
        $snapshot->created_at = $date;
        $snapshot->save();

        return $snapshot;
    }

    private function savingsSnapshot(string $date, float $total, array $balances): SavingsSnapshot
    {
        $snapshot = new SavingsSnapshot([
            'year' => 2026, 'month' => (int) date('n', strtotime($date)),
            'total_amount' => $total,
            'snapshot_data' => array_map(fn ($balance) => $balance + ['saver_id' => null], $balances),
        ]);
        $snapshot->created_at = $date;
        $snapshot->save();

        return $snapshot;
    }
}
