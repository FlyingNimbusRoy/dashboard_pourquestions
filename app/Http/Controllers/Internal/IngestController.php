<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Http\Requests\IngestGameSessionRequest;
use App\Models\DashGameSession;
use App\Models\DashGameQuestionResult;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IngestController extends Controller
{
    public function store(IngestGameSessionRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Idempotency: same game_id was already processed
        if (DashGameSession::where('game_id', $data['game_id'])->exists()) {
            return response()->json(['status' => 'already_processed'], 200);
        }

        $duration = null;
        if (! empty($data['started_at']) && ! empty($data['ended_at'])) {
            $duration = (int) Carbon::parse($data['started_at'])->diffInSeconds(Carbon::parse($data['ended_at']));
        }

        // Fetch current content versions for all questions in this session so
        // dash_game_question_results records the content era at ingest time.
        $questionIds = collect($data['questions'] ?? [])->pluck('id')->filter()->unique()->all();
        $versions = $questionIds
            ? DB::table('questions')->whereIn('id', $questionIds)->pluck('version', 'id')
            : collect();

        $orphans = array_diff($questionIds, $versions->keys()->all());
        if ($orphans) {
            Log::warning('[ingest] question_id(s) not found in questions table', [
                'game_id'    => $data['game_id'],
                'orphan_ids' => array_values($orphans),
            ]);
        }

        DB::transaction(function () use ($data, $duration, $versions) {
            $session = DashGameSession::create([
                'game_id'          => $data['game_id'],
                'pin'              => $data['pin'],
                'host_user_id'     => $data['host_user_id'] ?? null,
                'host_device_id'   => $data['host_device_id'] ?? null,
                'status'           => $data['status'],
                'schema_version'   => $data['schema_version'],
                'app_version'      => $data['app_version'],
                'player_count'     => count($data['players'] ?? []),
                'question_count'   => count($data['questions'] ?? []),
                'started_at'       => $data['started_at'] ?? null,
                'ended_at'         => $data['ended_at'] ?? null,
                'duration_s'       => $duration,
                'lead_changes'     => $data['lead_changes'] ?? 0,
                'last_phase_type'  => $data['last_phase']['type'] ?? null,
                'last_phase_round' => $data['last_phase']['round'] ?? null,
                'settings'         => $data['settings'],
                'payload'          => $data,
            ]);

            foreach ($data['questions'] ?? [] as $q) {
                DashGameQuestionResult::create([
                    'game_session_id'  => $session->id,
                    'question_id'      => $q['id'],
                    'question_version' => $versions->get($q['id'], 1),
                    'category_id'      => $q['category_id'] ?? null,
                    'round'            => $q['round'],
                    'correct_count'    => $q['correct'] ?? 0,
                    'wrong_count'      => $q['wrong'] ?? 0,
                    'timeout_count'    => $q['timeout'] ?? 0,
                    'option_counts'    => $q['option_counts'] ?? null,
                ]);
            }
        });

        return response()->json(['status' => 'accepted'], 201);
    }
}
