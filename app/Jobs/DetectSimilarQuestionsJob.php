<?php

namespace App\Jobs;

use App\Services\Similarity\SimilarityDetector;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DetectSimilarQuestionsJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public function __construct(public bool $full = false) {}

    public function handle(SimilarityDetector $detector): void
    {
        $detector->run($this->full);
    }
}
