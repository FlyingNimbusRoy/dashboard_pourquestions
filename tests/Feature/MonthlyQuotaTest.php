<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use App\Services\Insights\MonthlyQuota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MonthlyQuotaTest extends TestCase
{
    use RefreshDatabase;

    private User $maker;

    protected function setUp(): void
    {
        parent::setUp();
        // Pin to the 15th so subDays(4..14) always stay in the same month.
        Carbon::setTestNow(Carbon::now()->setDay(15)->startOfDay()->addHours(12));
        $this->maker = User::factory()->admin()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    private function makeQuestion(int $daysAgo): Question
    {
        return Question::factory()->create([
            'maker_id'   => $this->maker->id,
            'created_at' => now()->subDays($daysAgo),
        ]);
    }

    public function test_total_only_counts_current_month(): void
    {
        $this->makeQuestion(0); // today
        $this->makeQuestion(1); // yesterday
        Question::factory()->create(['maker_id' => $this->maker->id, 'created_at' => now()->subMonth()]);

        $data = app(MonthlyQuota::class)->get();

        $this->assertSame(2, $data['total']);
    }

    public function test_contributors_lists_admin_users_with_activity(): void
    {
        $nonAdmin = User::factory()->create();
        $this->makeQuestion(0);
        Question::factory()->create(['maker_id' => $nonAdmin->id, 'created_at' => now()]);

        $data = app(MonthlyQuota::class)->get();

        // Non-admins never appear; maker appears once
        $ids = $data['contributors']->pluck('id')->all();
        $this->assertContains($this->maker->id, $ids);
        $this->assertNotContains($nonAdmin->id, $ids);
    }

    public function test_current_streak_counts_consecutive_days_up_to_today(): void
    {
        // Contributions on days −2, −1, 0 → streak of 3
        $this->makeQuestion(2);
        $this->makeQuestion(1);
        $this->makeQuestion(0);

        $data = app(MonthlyQuota::class)->get();
        $contributor = $data['contributors']->firstWhere('id', $this->maker->id);

        $this->assertSame(3, $contributor->current_streak);
    }

    public function test_current_streak_resets_on_missed_day(): void
    {
        // Gap on day −2; only −1 and 0 form the current streak
        $this->makeQuestion(4);
        $this->makeQuestion(3);
        // day −2 missed
        $this->makeQuestion(1);
        $this->makeQuestion(0);

        $data = app(MonthlyQuota::class)->get();
        $contributor = $data['contributors']->firstWhere('id', $this->maker->id);

        $this->assertSame(2, $contributor->current_streak);
    }

    public function test_best_streak_is_longest_run_in_month(): void
    {
        // Streak of 3 (days −6,−5,−4), gap, streak of 2 (days −1, 0).
        $this->makeQuestion(6);
        $this->makeQuestion(5);
        $this->makeQuestion(4);
        // gap on days −3, −2
        $this->makeQuestion(1);
        $this->makeQuestion(0);

        $data = app(MonthlyQuota::class)->get();
        $contributor = $data['contributors']->firstWhere('id', $this->maker->id);

        $this->assertSame(2, $contributor->current_streak);
        $this->assertSame(3, $contributor->best_streak);
    }

    public function test_multiple_questions_same_day_count_as_one_streak_day(): void
    {
        // Two questions today, two yesterday — should be streak of 2, not 4
        $this->makeQuestion(0);
        $this->makeQuestion(0);
        $this->makeQuestion(1);
        $this->makeQuestion(1);

        $data = app(MonthlyQuota::class)->get();
        $contributor = $data['contributors']->firstWhere('id', $this->maker->id);

        $this->assertSame(2, $contributor->current_streak);
        $this->assertSame(2, $contributor->best_streak);
    }
}
