<?php

namespace Tests\Feature;

use App\Enums\ReviewOutcome;
use App\Enums\SimilarityStatus;
use App\Filament\Resources\QuestionSimilarities\Pages\ListQuestionSimilarities;
use App\Filament\Resources\Questions\Pages\CreateQuestion;
use App\Filament\Resources\Questions\Pages\ListQuestions;
use App\Filament\Resources\ReviewBatches\Pages\ReviewQuestions;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionSimilarity;
use App\Models\User;
use App\Services\Validation\ReviewBatchPlanner;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_players_cannot_use_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_every_page_renders_for_an_admin(): void
    {
        $question = Question::factory()->withAnswers('Parijs', 'Lyon')->create();
        $category = Category::factory()->create();
        $batch = app(ReviewBatchPlanner::class)->createBatch(reviewer: $this->admin);
        $this->actingAs($this->admin);

        foreach ([
            '/admin', '/admin/questions', '/admin/questions/create', "/admin/questions/{$question->id}/edit",
            '/admin/categories', "/admin/categories/{$category->id}/edit", '/admin/question-similarities',
            '/admin/review-batches', "/admin/review-batches/{$batch->id}", "/admin/review-batches/{$batch->id}/review",
            '/admin/needs-fix',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_bulk_move_to_category_from_the_question_list(): void
    {
        $target = Category::factory()->create();
        $questions = Question::factory(2)->create();

        Livewire::actingAs($this->admin)
            ->test(ListQuestions::class)
            ->selectTableRecords($questions)
            ->callAction(TestAction::make('moveCategory')->table()->bulk(), data: ['category_id' => $target->id])
            ->assertHasNoFormErrors()
            ->assertNotified('Moved 2 questions.');

        $this->assertSame(2, $target->questions()->count());
    }

    public function test_creating_a_question_requires_a_correct_answer(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateQuestion::class)
            ->fillForm([
                'vraag' => 'Hoeveel poten heeft een spin?',
                'difficulty' => 2,
                'answers' => [['answer' => '8', 'is_correct' => false], ['answer' => '6', 'is_correct' => false]],
            ])
            ->call('create')
            ->assertHasFormErrors(['answers']);

        $this->assertSame(0, Question::count());
    }

    public function test_reviewing_a_question_through_the_review_screen(): void
    {
        $question = Question::factory()->withAnswers('Parijs', 'Lyon')->create(['vraag' => 'Wat is de hoofdstad van Frankrijk?', 'difficulty' => 3]);
        $batch = app(ReviewBatchPlanner::class)->createBatch(reviewer: $this->admin);

        Livewire::actingAs($this->admin)
            ->test(ReviewQuestions::class, ['record' => $batch->id])
            ->assertFormSet(['vraag' => 'Wat is de hoofdstad van Frankrijk?'], 'form')
            ->fillForm(['vraag' => 'Wat is de hoofdstad van Frankrijk? '], 'form')
            ->fillForm(['difficulty_rating' => 1, 'answers_correct' => true, 'wording_clear' => true, 'category_correct' => true], 'verdictForm')
            ->call('submit', ReviewOutcome::Approved)
            ->assertRedirect();

        $question->refresh();
        $this->assertSame(1, $question->difficulty);
        $this->assertFalse($question->isValidationDue());
        $this->assertNotNull($batch->fresh()->completed_at);
    }

    public function test_dismissing_a_similar_pair(): void
    {
        [$a, $b] = Question::factory(2)->create();
        $pair = QuestionSimilarity::create(['question_id' => $a->id, 'similar_question_id' => $b->id, 'score' => 0.9, 'signals' => ['text' => 0.9], 'status' => SimilarityStatus::Open]);

        Livewire::actingAs($this->admin)
            ->test(ListQuestionSimilarities::class)
            ->callAction(TestAction::make('dismiss')->table($pair));

        $this->assertSame(SimilarityStatus::Dismissed, $pair->fresh()->status);
        $this->assertSame($this->admin->id, $pair->fresh()->reviewed_by);
    }
}
