<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionChange;
use App\Models\User;
use App\Services\Questions\QuestionMover;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_moving_questions_is_recorded_with_readable_labels(): void
    {
        $sport = Category::factory()->create(['name' => 'Sport']);
        $music = Category::factory()->create(['name' => 'Muziek']);
        $questions = Question::factory(2)->create(['category_id' => $sport->id]);
        $this->actingAs($user = User::factory()->admin()->create());

        $moved = app(QuestionMover::class)->moveToCategory($questions, $music->id);

        $this->assertSame(2, $moved);
        $this->assertSame(2, $music->questions()->count());
        $change = QuestionChange::where('question_id', $questions[0]->id)->sole();
        $this->assertSame($user->id, $change->user_id);
        $this->assertSame(['old' => $sport->id, 'new' => $music->id, 'old_label' => 'Sport', 'new_label' => 'Muziek'], $change->changes['category_id']);
    }

    public function test_questions_already_in_the_target_are_not_counted_or_logged(): void
    {
        $sport = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $sport->id]);

        $this->assertSame(0, app(QuestionMover::class)->moveToCategory([$question], $sport->id));
        $this->assertSame(0, QuestionChange::count());
    }

    public function test_merging_a_category_moves_all_its_questions(): void
    {
        [$from, $into] = Category::factory(2)->create();
        Question::factory(3)->create(['category_id' => $from->id]);

        $this->assertSame(3, app(QuestionMover::class)->mergeCategory($from, $into));
        $this->assertSame(0, $from->questions()->count());
        $this->assertSame(3, $into->questions()->count());
    }

    public function test_deleting_a_question_removes_its_answers_and_keeps_a_snapshot(): void
    {
        $question = Question::factory()->withAnswers('Parijs', 'Lyon')->create(['vraag' => 'Hoofdstad van Frankrijk?']);

        $question->delete();

        $this->assertSame(0, Answer::where('question_id', $question->id)->count());
        $change = QuestionChange::where('question_id', $question->id)->where('action', 'deleted')->sole();
        $this->assertSame('Hoofdstad van Frankrijk?', $change->changes['snapshot']['vraag']);
        $this->assertCount(2, $change->changes['answers']);
    }

    public function test_editing_an_answer_marks_the_question_for_a_new_similarity_check(): void
    {
        $question = Question::factory()->withAnswers('Parijs')->create();
        $question->forceFill(['last_compared_at' => now()])->saveQuietly();
        $this->travel(1)->minutes();

        $question->answers()->first()->update(['answer' => 'Lyon']);

        $this->assertTrue($question->fresh()->updated_at->gt($question->fresh()->last_compared_at));
        $this->assertDatabaseHas('dash_question_changes', ['question_id' => $question->id, 'action' => 'answer_updated']);
    }
}
