<?php

namespace App\Console\Commands;

use App\Services\Crud\GameService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('games:close-stale')]
#[Description('Mark stale games (open or running past the inactivity threshold) as finished')]
class CloseStaleGames extends Command
{
    public function handle(GameService $svc): int
    {
        $n = $svc->finishAllStale();

        $n > 0
            ? $this->components->info("Marked {$n} stale game(s) as finished.")
            : $this->components->info('No stale games found.');

        return self::SUCCESS;
    }
}
