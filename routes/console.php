<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run daily so a preference of 29-31 still captures on the final day of shorter months.
Schedule::command('economy:capture-snapshot')->dailyAt('00:00');
Schedule::command('kids:generate-recurring')->dailyAt('00:00');
