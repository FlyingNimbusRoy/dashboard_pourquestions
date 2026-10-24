<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\DashLiveSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveSnapshotController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'open_rooms'       => ['required', 'integer', 'min:0'],
            'players_online'   => ['required', 'integer', 'min:0'],
            'games_today'      => ['nullable', 'integer', 'min:0'],
            'rooms_per_phase'  => ['nullable', 'array'],
            'failed_post_count' => ['nullable', 'integer', 'min:0'],
            'retry_queue_size'  => ['nullable', 'integer', 'min:0'],
        ]);

        DashLiveSnapshot::create([
            'open_rooms'        => $data['open_rooms'],
            'players_online'    => $data['players_online'],
            'games_today'       => $data['games_today'] ?? 0,
            'rooms_per_phase'   => $data['rooms_per_phase'] ?? null,
            'failed_post_count' => min($data['failed_post_count'] ?? 0, 255),
            'retry_queue_size'  => min($data['retry_queue_size'] ?? 0, 255),
            'snapshot_at'       => now(),
        ]);

        return response()->json(['status' => 'ok'], 201);
    }
}
