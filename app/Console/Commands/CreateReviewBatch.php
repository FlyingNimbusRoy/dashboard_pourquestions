<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Validation\ReviewBatchPlanner;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reviews:create-batch
    {--size= : Number of questions (default: config dashboard.validation.batch_size)}
    {--category= : Only questions from this category id}
    {--reviewer= : Reviewer e-mail (default: the reviewer with the fewest open batches)}
    {--scheduled : Only create a batch when fewer than the maximum number of batches are open}')]
#[Description('Put questions that are due for revalidation into a review batch')]
class CreateReviewBatch extends Command
{
    public function handle(ReviewBatchPlanner $planner): int
    {
        if ($this->option('scheduled')) {
            $batch = $planner->scheduleBatch();
        } else {
            $reviewer = null;
            if ($email = $this->option('reviewer')) {
                $reviewer = User::reviewers()->where('email', $email)->first();
                if ($reviewer === null) {
                    $this->components->error("No reviewer (admin) with e-mail {$email}.");

                    return self::FAILURE;
                }
            }

            $batch = $planner->createBatch(
                reviewer: $reviewer,
                size: $this->option('size') ? (int) $this->option('size') : null,
                categoryId: $this->option('category') ? (int) $this->option('category') : null,
            );
        }

        if ($batch === null) {
            $this->components->info('No batch created: nothing is due, or the maximum number of open batches is reached.');

            return self::SUCCESS;
        }

        $this->components->info(sprintf(
            'Created "%s" with %d questions for %s.',
            $batch->name, $batch->reviews()->count(), $batch->reviewer?->name ?? 'any reviewer',
        ));

        return self::SUCCESS;
    }
}
