<?php

namespace Tests\Feature;

use App\Enums\ReviewOutcome;
use App\Models\Question;
use App\Models\ReviewBatch;
use App\Models\User;
use App\Services\Validation\ReviewBatchPlanner;
use App\Services\Validation\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private const OK = ['difficulty_rating' => 4, 'answers_correct' => true, 'wording_clear' => true, 'category_correct' => true];

    public function test_batches_take_the_longest_unvalidated_questions_first(): void
    {
        $fresh = Question::factory()->validated()->create();
        $old = Question::factory()->validated(now()->subYears(3))->create();
        $never = Question::factory()->create();
        $older = Question::factory()->validated(now()->subYears(2))->create();

        $batch = $this->planner()->createBatch(size: 2);

        $this->assertSame([$never->id, $old->id], $batch->reviews()->orderBy('id')->pluck('question_id')->all());
        $this->assertNotContains($fresh->id, $batch->reviews()->pluck('question_id'));
        $this->assertNotContains($older->id, $batch->reviews()->pluck('question_id'));
    }

    public function test_questions_waiting_for_review_are_not_picked_again(): void
    {
        Question::factory(3)->create();

        $first = $this->planner()->createBatch(size: 2);
        $second = $this->planner()->createBatch(size: 2);

        $this->assertSame(1, $second->reviews()->count());
        $this->assertEmpty(array_intersect($first->reviews()->pluck('question_id')->all(), $second->reviews()->pluck('question_id')->all()));
        $this->assertNull($this->planner()->createBatch(size: 2));
    }

    public function test_batches_go_to_the_reviewer_with_the_fewest_open_batches(): void
    {
        Question::factory(4)->create();
        $busy = User::factory()->admin()->create();
        $free = User::factory()->admin()->create();
        User::factory()->create(); // players are never reviewers
        ReviewBatch::create(['name' => 'Existing', 'reviewer_id' => $busy->id]);

        $this->assertTrue($this->planner()->createBatch(size: 1)->reviewer->is($free));
    }

    public function test_the_scheduler_stops_while_too_many_batches_are_open(): void
    {
        config(['dashboard.validation.max_open_batches' => 1, 'dashboard.validation.batch_size' => 1]);
        Question::factory(3)->create();

        $this->assertNotNull($this->planner()->scheduleBatch());
        $this->assertNull($this->planner()->scheduleBatch());
    }

    public function test_approving_renews_validation_and_applies_difficulty(): void
    {
        [$batch, $review, $reviewer] = $this->batchWithOneQuestion();

        app(ReviewService::class)->submit($review, $reviewer, ReviewOutcome::Approved, self::OK);

        $question = $review->question->fresh();
        $this->assertSame(4, $question->difficulty);
        $this->assertFalse($question->isValidationDue());
        $this->assertSame(ReviewOutcome::Approved, $review->fresh()->outcome);
        $this->assertNotNull($batch->fresh()->completed_at);
        $this->assertDatabaseHas('dash_question_changes', ['question_id' => $question->id, 'action' => 'updated']);
    }

    public function test_a_question_with_problems_cannot_be_approved(): void
    {
        [, $review, $reviewer] = $this->batchWithOneQuestion();

        $this->expectException(ValidationException::class);
        app(ReviewService::class)->submit($review, $reviewer, ReviewOutcome::Approved, ['answers_correct' => false] + self::OK);
    }

    public function test_needs_fix_requires_notes_and_does_not_validate_until_resolved(): void
    {
        [, $review, $reviewer] = $this->batchWithOneQuestion();
        $service = app(ReviewService::class);

        try {
            $service->submit($review, $reviewer, ReviewOutcome::NeedsFix, ['answers_correct' => false] + self::OK);
            $this->fail('Needs fix without notes should be rejected.');
        } catch (ValidationException) {
        }

        $service->submit($review, $reviewer, ReviewOutcome::NeedsFix, ['answers_correct' => false, 'notes' => 'Answer B is also correct.'] + self::OK);
        $this->assertTrue($review->question->fresh()->isValidationDue());

        $service->resolve($review->fresh());
        $this->assertFalse($review->question->fresh()->isValidationDue());
        $this->assertNotNull($review->fresh()->resolved_at);
    }

    public function test_flagged_questions_are_not_put_in_a_new_batch_until_fixed(): void
    {
        [, $review, $reviewer] = $this->batchWithOneQuestion();
        app(ReviewService::class)->submit($review, $reviewer, ReviewOutcome::NeedsFix, ['notes' => 'Typo'] + self::OK);

        $this->assertNull($this->planner()->createBatch());
    }

    public function test_skipped_questions_return_to_the_pool(): void
    {
        [, $review, $reviewer] = $this->batchWithOneQuestion();
        app(ReviewService::class)->skip($review, $reviewer);

        $this->assertSame($review->question_id, $this->planner()->createBatch()->reviews()->first()->question_id);
    }

    /** @return array{0: ReviewBatch, 1: \App\Models\QuestionReview, 2: User} */
    private function batchWithOneQuestion(): array
    {
        Question::factory()->withAnswers('Ja', 'Nee')->create(['difficulty' => 2]);
        $reviewer = User::factory()->admin()->create();
        $batch = $this->planner()->createBatch(reviewer: $reviewer);

        return [$batch, $batch->reviews()->first(), $reviewer];
    }

    private function planner(): ReviewBatchPlanner
    {
        return app(ReviewBatchPlanner::class);
    }
}
