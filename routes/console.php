<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

try {
    $snapshotDay = \App\Models\Setting::get('economy_snapshot_day', 25);
} catch (\Exception $e) {
    $snapshotDay = 25;
}

Schedule::command('economy:capture-snapshot')->monthlyOn($snapshotDay, '00:00');
Schedule::command('kids:generate-recurring')->dailyAt('00:00');
