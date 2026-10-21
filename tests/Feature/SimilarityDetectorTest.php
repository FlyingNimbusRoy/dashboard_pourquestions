<?php

namespace Tests\Feature;

use App\Enums\SimilarityStatus;
use App\Models\Question;
use App\Models\QuestionSimilarity;
use App\Services\Similarity\SimilarityDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimilarityDetectorTest extends TestCase
{
    use RefreshDatabase;

    private Question $paris;

    private Question $parisAgain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paris = Question::factory()->withAnswers('Parijs', 'Lyon')->create(['vraag' => 'Wat is de hoofdstad van Frankrijk?']);
        $this->parisAgain = Question::factory()->withAnswers('Parijs', 'Nice')->create(['vraag' => 'Welke stad is de hoofdstad van Frankrijk?']);
        Question::factory()->withAnswers('Berlijn', 'Bonn')->create(['vraag' => 'Wat is de hoofdstad van Duitsland?']);
        Question::factory()->withAnswers('8', '6')->create(['vraag' => 'Hoeveel poten heeft een spin?']);
    }

    public function test_finds_the_duplicate_pair_once_in_canonical_order(): void
    {
        $stats = $this->detector()->run();

        $this->assertSame(4, $stats['compared']);
        $this->assertSame(1, QuestionSimilarity::count());
        $pair = QuestionSimilarity::first();
        $this->assertSame([$this->paris->id, $this->parisAgain->id], [$pair->question_id, $pair->similar_question_id]);
        $this->assertSame(SimilarityStatus::Open, $pair->status);
        $this->assertEquals(1, $pair->signals['correct_answer']);
    }

    public function test_a_second_run_only_compares_changed_questions(): void
    {
        $this->detector()->run();

        $this->assertSame(0, $this->detector()->run()['compared']);

        $this->travel(1)->minutes();
        $this->paris->update(['trivia' => 'Parijs heet ook de lichtstad.']);

        $this->assertSame(1, $this->detector()->run()['compared']);
    }

    public function test_a_dismissed_pair_stays_dismissed_until_one_question_is_edited(): void
    {
        $this->detector()->run();
        QuestionSimilarity::first()->update(['status' => SimilarityStatus::Dismissed, 'reviewed_at' => now()]);

        $this->detector()->run(full: true);
        $this->assertSame(SimilarityStatus::Dismissed, QuestionSimilarity::first()->status);

        $this->travel(1)->minutes();
        $this->parisAgain->update(['vraag' => 'Welke stad is eigenlijk de hoofdstad van Frankrijk?']);
        $stats = $this->detector()->run();

        $this->assertSame(1, $stats['reopened']);
        $this->assertSame(SimilarityStatus::Open, QuestionSimilarity::first()->status);
    }

    public function test_an_open_pair_disappears_when_the_question_is_rewritten(): void
    {
        $this->detector()->run();

        $this->travel(1)->minutes();
        $this->parisAgain->update(['vraag' => 'Welke rivier stroomt door Parijs?']);
        $this->parisAgain->answers()->where('is_correct', true)->first()->update(['answer' => 'Seine']);

        $this->assertSame(1, $this->detector()->run()['removed']);
        $this->assertSame(0, QuestionSimilarity::count());
    }

    public function test_live_hint_finds_an_existing_question_while_typing(): void
    {
        $matches = $this->detector()->findSimilarTo('Wat is toch de hoofdstad van Frankrijk', [['answer' => 'Parijs', 'is_correct' => true]]);

        $this->assertContains($this->paris->id, array_column($matches, 'question_id'));
        $this->assertNotContains($this->paris->id, array_column(
            $this->detector()->findSimilarTo('Wat is de hoofdstad van Frankrijk?', excludeId: $this->paris->id),
            'question_id',
        ));
    }

    private function detector(): SimilarityDetector
    {
        $detector = app(SimilarityDetector::class);
        $detector->forgetCorpus();

        return $detector;
    }
}
