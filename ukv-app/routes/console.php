<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// --- UKV scheduled tasks ---
\Illuminate\Support\Facades\Schedule::command('ukv:purge-documents')->daily();
\Illuminate\Support\Facades\Schedule::command('ukv:purge-checklists')->daily();
\Illuminate\Support\Facades\Schedule::command('ukv:reconcile-stripe')->dailyAt('06:00')->withoutOverlapping();
\Illuminate\Support\Facades\Schedule::command('ukv:owner-digest')->dailyAt('08:00');

// --- Guide engine: freshness (Module B) + AI change-detection (Module C) ---
\Illuminate\Support\Facades\Schedule::command('destinations:freshness')->dailyAt('07:00');
\Illuminate\Support\Facades\Schedule::command('destinations:check-changes')->weekly()->mondays()->at('05:00')->withoutOverlapping();

// Release expired appointment-slot holds back to the available pool.
\Illuminate\Support\Facades\Schedule::command('slots:release-expired')->everyFiveMinutes();
