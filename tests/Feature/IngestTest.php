<?php

namespace Tests\Feature;

use App\Models\DashGameQuestionResult;
use App\Models\DashGameSession;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class IngestTest extends TestCase
{
    use RefreshDatabase;

    private function post(array $payload): TestResponse
    {
        config(['dashboard.ingest.secret' => 'test-secret']);
        return $this->withHeaders(['X-Ingest-Secret' => 'test-secret'])
                    ->postJson('/internal/game-session', $payload);
    }

    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'schema_version' => 1,
            'app_version'    => '1.2.3',
            'game_id'        => rand(100000, 999999),
            'pin'            => 'TEST1',
            'status'         => 'completed',
            'settings'       => [],
        ], $overrides);
    }

    public function test_duplicate_game_id_on_ingest_returns_already_processed(): void
    {
        config(['dashboard.ingest.secret' => 'test-secret']);

        $payload = [
            'schema_version' => 1,
            'app_version'    => '1.0.0',
            'game_id'        => 12345,
            'pin'            => 'ABCD1',
            'status'         => 'completed',
            'settings'       => ['foo' => 'bar'],
        ];

        $first = $this->withHeaders(['X-Ingest-Secret' => 'test-secret'])
            ->postJson('/internal/game-session', $payload);

        $first->assertStatus(201)->assertJson(['status' => 'accepted']);

        $second = $this->withHeaders(['X-Ingest-Secret' => 'test-secret'])
            ->postJson('/internal/game-session', $payload);

        $second->assertStatus(200)->assertJson(['status' => 'already_processed']);
    }

    public function test_ingest_without_secret_is_rejected(): void
    {
        $response = $this->postJson('/internal/game-session', [
            'schema_version' => 1,
            'app_version'    => '1.0.0',
            'game_id'        => 99999,
            'pin'            => 'ZZZZ9',
            'status'         => 'completed',
            'settings'       => [],
        ]);

        $response->assertStatus(403);
    }

    public function test_session_fields_are_stored_correctly(): void
    {
        $this->post($this->basePayload([
            'game_id'     => 55001,
            'started_at'  => '2026-10-20 20:00:00',
            'ended_at'    => '2026-10-20 20:05:00', // 300 seconds
            'lead_changes' => 4,
            'last_phase'  => ['type' => 'question', 'round' => 7],
            'players'     => [['id' => 1], ['id' => 2], ['id' => 3]],
            'questions'   => [],
        ]))->assertStatus(201);

        $session = DashGameSession::where('game_id', 55001)->firstOrFail();
        $this->assertSame(300, $session->duration_s);
        $this->assertSame(4, $session->lead_changes);
        $this->assertSame('question', $session->last_phase_type);
        $this->assertSame(7, $session->last_phase_round);
        $this->assertSame(3, $session->player_count);
        $this->assertSame(0, $session->question_count);
    }

    public function test_question_results_are_stored_with_version_snapshot(): void
    {
        $question = Question::factory()->create();
        DB::table('questions')->where('id', $question->id)->update(['version' => 3]);

        $this->post($this->basePayload([
            'questions' => [[
                'id'      => $question->id,
                'round'   => 1,
                'correct' => 6,
                'wrong'   => 2,
                'timeout' => 1,
                'option_counts' => [1 => 6, 2 => 2, 3 => 0, 4 => 1],
            ]],
        ]))->assertStatus(201);

        $session = DashGameSession::first();
        $this->assertSame(1, $session->question_count);

        $result = DashGameQuestionResult::first();
        $this->assertSame($question->id, $result->question_id);
        $this->assertSame(3, $result->question_version);
        $this->assertSame(6, $result->correct_count);
        $this->assertSame(2, $result->wrong_count);
        $this->assertSame(1, $result->timeout_count);
        $this->assertSame([1 => 6, 2 => 2, 3 => 0, 4 => 1], $result->option_counts);
    }

    public function test_orphan_question_id_does_not_block_ingest(): void
    {
        $this->post($this->basePayload([
            'questions' => [
                ['id' => 999999, 'round' => 1, 'correct' => 0, 'wrong' => 5, 'timeout' => 0],
            ],
        ]))->assertStatus(201)->assertJson(['status' => 'accepted']);

        $this->assertSame(1, DashGameSession::count());
        // Orphan question still gets a result row with default version 1
        $this->assertSame(1, DashGameQuestionResult::count());
        $this->assertSame(1, (int) DashGameQuestionResult::value('question_version'));
    }

    public function test_ingest_without_questions_stores_session_only(): void
    {
        $this->post($this->basePayload())->assertStatus(201);

        $this->assertSame(1, DashGameSession::count());
        $this->assertSame(0, DashGameQuestionResult::count());
    }
}
