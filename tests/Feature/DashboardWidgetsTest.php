<?php

namespace Tests\Feature;

use App\Enums\ReviewOutcome;
use App\Filament\Resources\ReviewBatches\ReviewBatchResource;
use App\Filament\Widgets\DifficultyDistributionChart;
use App\Filament\Widgets\MyOpenReviews;
use App\Filament\Widgets\ValidationProgressChart;
use App\Models\Question;
use App\Models\QuestionReview;
use App\Models\ReviewBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    private function review(ReviewBatch $batch, Question $question, ReviewOutcome $outcome, $at = null): QuestionReview
    {
        return QuestionReview::create([
            'review_batch_id' => $batch->id, 'question_id' => $question->id, 'reviewer_id' => $this->admin->id,
            'outcome' => $outcome, 'difficulty_before' => 3, 'reviewed_at' => $at,
        ]);
    }

    public function test_dashboard_renders_with_data_and_empty(): void
    {
        $this->get('/admin')->assertOk()->assertSee('My open reviews');

        $batch = ReviewBatch::create(['name' => 'Batch A', 'reviewer_id' => $this->admin->id, 'due_at' => now()->addDay()]);
        $this->review($batch, Question::factory()->create(), ReviewOutcome::Approved, now());

        $this->get('/admin')->assertOk()->assertSee('Batch A')->assertSee('Questions reviewed per week');
    }

    public function test_validation_progress_counts_per_week_and_excludes_skipped(): void
    {
        $batch = ReviewBatch::create(['name' => 'B', 'reviewer_id' => $this->admin->id]);
        $this->review($batch, Question::factory()->create(), ReviewOutcome::Approved, now());
        $this->review($batch, Question::factory()->create(), ReviewOutcome::NeedsFix, now());
        $this->review($batch, Question::factory()->create(), ReviewOutcome::Skipped, now());
        $this->review($batch, Question::factory()->create(), ReviewOutcome::Approved, now()->subWeeks(20));

        $component = Livewire::test(ValidationProgressChart::class)->instance();
        $data = (new \ReflectionMethod($component, 'getData'))->invoke($component);

        $this->assertCount(12, $data['labels']);
        $this->assertSame(now()->startOfWeek()->format('M j'), $data['labels'][11]);
        $this->assertSame(2, $data['datasets'][0]['data'][11]);
        $this->assertSame(2, array_sum($data['datasets'][0]['data']));
    }

    public function test_difficulty_distribution_covers_every_level(): void
    {
        Question::factory()->count(2)->create(['difficulty' => 1]);
        Question::factory()->create(['difficulty' => 5]);

        $component = Livewire::test(DifficultyDistributionChart::class)->instance();
        $data = (new \ReflectionMethod($component, 'getData'))->invoke($component);

        $this->assertSame(['1', '2', '3', '4', '5'], $data['labels']);
        $this->assertSame([2, 0, 0, 0, 1], $data['datasets'][0]['data']);
    }

    public function test_my_open_reviews_lists_own_and_unassigned_open_batches_only(): void
    {
        $other = User::factory()->admin()->create();
        $mine = ReviewBatch::create(['name' => 'Mine', 'reviewer_id' => $this->admin->id, 'due_at' => now()->subDay()]);
        $free = ReviewBatch::create(['name' => 'Unassigned']);
        $theirs = ReviewBatch::create(['name' => 'Theirs', 'reviewer_id' => $other->id]);
        $done = ReviewBatch::create(['name' => 'Done', 'reviewer_id' => $this->admin->id, 'completed_at' => now()]);
        $this->review($mine, Question::factory()->create(), ReviewOutcome::Approved, now());
        $this->review($mine, Question::factory()->create(), ReviewOutcome::Pending);

        Livewire::test(MyOpenReviews::class)
            ->assertCanSeeTableRecords([$mine, $free])
            ->assertCanNotSeeTableRecords([$theirs, $done])
            ->assertSee('1 / 2')
            ->assertSee(ReviewBatchResource::getUrl('review', ['record' => $mine]));
    }

    public function test_my_open_reviews_shows_empty_state(): void
    {
        Livewire::test(MyOpenReviews::class)->assertSee('No open reviews');
    }
}
