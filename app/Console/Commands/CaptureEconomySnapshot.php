<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Models\EconomySnapshot;
use App\Services\EconomySnapshotService;
use Carbon\Carbon;

class CaptureEconomySnapshot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'economy:capture-snapshot {--force : Force the snapshot even if not the current day}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically capture the current economy state as a monthly snapshot.';

    /**
     * Execute the console command.
     */
    public function handle(EconomySnapshotService $service)
    {
        $snapshotDay = (int) Setting::get('economy_snapshot_day', 25);
        $today = now()->day;
        $month = now()->month;
        $year = now()->year;

        if ($this->option('force') || $today === $snapshotDay) {
            // Check if we already have a snapshot for this month
            $exists = EconomySnapshot::where('month', $month)
                ->where('year', $year)
                ->exists();

            if ($exists && !$this->option('force')) {
                $this->info("Snapshot already exists for {$month}/{$year}.");
                return;
            }

            $service->capture();
            $this->info("Economy snapshot captured successfully for {$month}/{$year}.");
        } else {
            $this->info("Today is not the snapshot day ({$snapshotDay}). skipping...");
        }
    }
}
