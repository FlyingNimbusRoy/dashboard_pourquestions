<?php

namespace App\Providers;

use App\Services\Similarity\TextAnalyzer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Shared so its stem cache is reused across a whole similarity run.
        $this->app->singleton(TextAnalyzer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The schema is shared with the live game: never allow migrate:fresh / db:wipe there.
        DB::prohibitDestructiveCommands($this->app->isProduction());
    }
}
