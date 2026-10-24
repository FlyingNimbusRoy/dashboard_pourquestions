<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Game;
use App\Models\Question;
use App\Models\User;
use App\Services\Crud\QuestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for QuestionService::delete() regarding game usage.
 *
 * FINDING (2026-10-24): QuestionService::beforeDelete() does NOT guard against
 * deleting a question that has been used in a game. The only action in beforeDelete()
 * is cascading deletion of the question's answers. Game usage is tracked externally
 * via QuestionUsage (a read-through cache over games.question_array) and is never
 * consulted during a delete. Therefore QuestionService::delete() always succeeds
 * regardless of usage history.
 *
 * Consequence: a question that was asked in games can be silently deleted. Its id
 * will become an orphan inside games.question_array and is counted separately by
 * QuestionUsage::orphanIds(). This is not currently guarded at the service layer.
 *
 * If a guard is ever added, these tests will need to be revised.
 */
class QuestionBulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function service(): QuestionService
    {
        return app(QuestionService::class);
    }

    // -------------------------------------------------------------------------
    // 1. Delete a question that has been used in a game — no guard, succeeds
    //
    //    Usage is tracked in games.question_array (JSON), NOT in a column on
    //    questions. The service has no usage guard; delete always proceeds.
    // -------------------------------------------------------------------------

    public function test_deleting_a_question_that_was_used_in_a_game_succeeds_without_error(): void
    {
        $question = Question::factory()
            ->withAnswers('Correct', 'Wrong')
            ->create(['vraag' => 'Used in a game?']);

        // Record that this question appeared in a game (games.question_array)
        Game::factory()->create(['question_array' => [$question->id], 'is_finished' => true]);

        // Service has no usage guard — delete should succeed
        $this->service()->delete($question);

        $this->assertDatabaseMissing('questions', ['id' => $question->id]);

        // Answers are also cleaned up (behaviour from beforeDelete)
        $this->assertSame(0, Answer::where('question_id', $question->id)->count());
    }

    // -------------------------------------------------------------------------
    // 2. Delete a never-asked question — succeeds and removes answers
    // -------------------------------------------------------------------------

    public function test_deleting_a_never_asked_question_succeeds_and_removes_answers(): void
    {
        $question = Question::factory()
            ->withAnswers('A', 'B', 'C')
            ->create(['vraag' => 'Never asked?']);

        $this->service()->delete($question);

        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
        $this->assertSame(0, Answer::where('question_id', $question->id)->count());
    }
}
