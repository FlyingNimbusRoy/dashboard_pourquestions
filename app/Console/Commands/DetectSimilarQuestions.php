<?php

namespace App\Console\Commands;

use App\Services\Similarity\SimilarityDetector;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('questions:detect-similar {--full : Compare every question, not only the ones changed since the last run}')]
#[Description('Find similar or duplicate questions and queue them for review')]
class DetectSimilarQuestions extends Command
{
    public function handle(SimilarityDetector $detector): int
    {
        $started = microtime(true);
        $stats = $detector->run((bool) $this->option('full'));

        $this->components->info(sprintf(
            'Compared %d questions in %.1fs: %d similar pairs (%d new, %d reopened, %d no longer similar).',
            $stats['compared'], microtime(true) - $started, $stats['found'], $stats['created'], $stats['reopened'], $stats['removed'],
        ));

        return self::SUCCESS;
    }
}
