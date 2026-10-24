<?php

namespace Tests\Feature;

use App\Models\DashIssue;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AnalyticsThresholdCheckTest extends TestCase
{
    use RefreshDatabase;

    private function seedSession(): int
    {
        return DB::table('dash_game_sessions')->insertGetId([
            'game_id'     => rand(1, 999999),
            'pin'         => 'TEST1',
            'status'      => 'completed',
            'app_version' => '1.0.0',
            'settings'    => '{}',
            'payload'     => '{}',
            'ingested_at' => now(),
        ]);
    }

    private function seedResult(int $sessionId, int $questionId, int $correct, int $wrong, int $timeout): void
    {
        DB::table('dash_game_question_results')->insert([
            'game_session_id' => $sessionId,
            'question_id'     => $questionId,
            'round'           => 1,
            'correct_count'   => $correct,
            'wrong_count'     => $wrong,
            'timeout_count'   => $timeout,
        ]);
    }

    public function test_low_correct_rate_raises_balance_issue(): void
    {
        $question = Question::factory()->create();
        $session = $this->seedSession();
        // 2 correct, 8 wrong = 20% correct — exactly at the default 0.20 threshold
        // We need it BELOW 0.20, so: 1 correct, 9 wrong = 10%
        $this->seedResult($session, $question->id, 1, 9, 0);

        $this->artisan('analytics:check-thresholds', ['--min-samples' => 1])
             ->assertSuccessful();

        $this->assertDatabaseHas('dash_issues', [
            'subject_type' => 'question',
            'subject_id'   => $question->id,
            'reason_code'  => 'low_correct_rate',
            'type'         => 'balance',
        ]);
    }

    public function test_high_correct_rate_raises_balance_issue(): void
    {
        $question = Question::factory()->create();
        $session = $this->seedSession();
        // 10 correct, 0 wrong = 100% — above the default 0.95 threshold
        $this->seedResult($session, $question->id, 10, 0, 0);

        $this->artisan('analytics:check-thresholds', ['--min-samples' => 1])
             ->assertSuccessful();

        $this->assertDatabaseHas('dash_issues', [
            'subject_type' => 'question',
            'subject_id'   => $question->id,
            'reason_code'  => 'high_correct_rate',
            'type'         => 'balance',
        ]);
    }

    public function test_high_timeout_rate_raises_content_issue(): void
    {
        $question = Question::factory()->create();
        $session = $this->seedSession();
        // 0 correct, 0 wrong, 10 timeout = 100% — above the default 0.40 threshold
        $this->seedResult($session, $question->id, 0, 0, 10);

        $this->artisan('analytics:check-thresholds', ['--min-samples' => 1])
             ->assertSuccessful();

        $this->assertDatabaseHas('dash_issues', [
            'subject_type' => 'question',
            'subject_id'   => $question->id,
            'reason_code'  => 'high_timeout_rate',
            'type'         => 'content',
        ]);
    }

    public function test_healthy_question_raises_no_issue(): void
    {
        $question = Question::factory()->create();
        $session = $this->seedSession();
        // 7 correct, 3 wrong = 70% — between 0.20 and 0.95
        $this->seedResult($session, $question->id, 7, 3, 0);

        $this->artisan('analytics:check-thresholds', ['--min-samples' => 1])
             ->assertSuccessful();

        $this->assertDatabaseMissing('dash_issues', ['subject_id' => $question->id]);
    }

    public function test_min_samples_filter_skips_insufficient_data(): void
    {
        $question = Question::factory()->create();
        $session = $this->seedSession();
        $this->seedResult($session, $question->id, 1, 9, 0); // 10% correct — would breach, but only 1 game

        // Default min_samples is 10; with only 1 game session, no issue should be raised
        $this->artisan('analytics:check-thresholds')->assertSuccessful();

        $this->assertDatabaseMissing('dash_issues', ['subject_id' => $question->id]);
    }

    public function test_running_twice_does_not_duplicate_issues(): void
    {
        $question = Question::factory()->create();
        $session = $this->seedSession();
        $this->seedResult($session, $question->id, 1, 9, 0);

        $this->artisan('analytics:check-thresholds', ['--min-samples' => 1])->assertSuccessful();
        $this->artisan('analytics:check-thresholds', ['--min-samples' => 1])->assertSuccessful();

        $this->assertSame(1, DashIssue::where('subject_id', $question->id)->where('reason_code', 'low_correct_rate')->count());
    }
}
