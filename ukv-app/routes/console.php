<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// --- UKV scheduled tasks ---
\Illuminate\Support\Facades\Schedule::command('ukv:purge-documents')->daily();
\Illuminate\Support\Facades\Schedule::command('ukv:reconcile-stripe')->dailyAt('06:00')->withoutOverlapping();
\Illuminate\Support\Facades\Schedule::command('ukv:owner-digest')->dailyAt('08:00');
