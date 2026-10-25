<?php

use Illuminate\Support\Facades\Schedule;

// On shared hosting a single cron entry runs this: * * * * * php artisan schedule:run

// Quick incremental pass for new or edited questions; a weekly full pass re-calibrates
// all scores as word frequencies across the question set change.
Schedule::command('questions:detect-similar')->hourly()->withoutOverlapping();
Schedule::command('questions:detect-similar --full')->weeklyOn(0, '03:00')->withoutOverlapping();

// New review batch every Monday morning, unless reviewers still have enough open work.
Schedule::command('reviews:create-batch --scheduled')->weeklyOn(1, '07:00');

if (config('dashboard.queue_via_scheduler')) {
    Schedule::command('queue:work --stop-when-empty --max-time=50')->everyMinute()->withoutOverlapping();
}

// Analytics pipeline: rollup first, then threshold check (depends on session data being fresh).
Schedule::command('analytics:rollup-daily')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('analytics:check-thresholds')->dailyAt('02:30')->withoutOverlapping();

// Close games that have been open/running past the inactivity threshold (config: games.stale_after_hours).
Schedule::command('games:close-stale')->hourly()->withoutOverlapping();
