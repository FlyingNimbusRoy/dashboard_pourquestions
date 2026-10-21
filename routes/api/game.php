<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

// Game domain: players, games, achievements, presets, purchases, buddies, comments, stats.
Route::get('players/{id}/achievements', [Api\PlayerController::class, 'achievements'])->name('players.achievements');
Route::get('players/{id}/games', [Api\PlayerController::class, 'games'])->name('players.games');
Route::apiResource('achievements', Api\AchievementController::class)->only(['index', 'show', 'store', 'update', 'destroy'])->parameters(['achievements' => 'id']);
Route::apiResource('game-presets', Api\GamePresetController::class)->only(['index', 'show', 'store', 'update', 'destroy'])->parameters(['game-presets' => 'id']);
Route::apiResource('setup-presets', Api\SetupPresetController::class)->only(['index', 'show', 'store', 'update', 'destroy'])->parameters(['setup-presets' => 'id']);
Route::apiResource('gamepack-purchases', Api\GamepackPurchaseController::class)->only(['index', 'show', 'store', 'destroy'])->parameters(['gamepack-purchases' => 'id']);
Route::apiResource('booze-buddies', Api\BoozeBuddyController::class)->only(['index', 'show', 'store', 'update', 'destroy'])->parameters(['booze-buddies' => 'id']);
Route::apiResource('comments', Api\CommentController::class)->only(['index', 'show', 'store', 'update', 'destroy'])->parameters(['comments' => 'id']);
Route::apiResource('gamestats', Api\GamestatsController::class)->only(['index', 'show', 'update'])->parameters(['gamestats' => 'id']);
Route::apiResource('games', Api\GameController::class)->only(['index', 'show', 'update'])->parameters(['games' => 'id']);
Route::apiResource('players', Api\PlayerController::class)->only(['index', 'show', 'update'])->parameters(['players' => 'id']);
