<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('economy:capture-snapshot')->monthlyOn(\App\Models\Setting::get('economy_snapshot_day', 25), '00:00');
