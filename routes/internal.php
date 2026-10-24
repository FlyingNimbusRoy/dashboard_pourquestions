<?php

use App\Http\Controllers\Internal\IngestController;
use App\Http\Controllers\Internal\LiveSnapshotController;
use Illuminate\Support\Facades\Route;

Route::post('game-session', [IngestController::class, 'store'])->name('game-session.store');
Route::post('live-snapshot', [LiveSnapshotController::class, 'store'])->name('live-snapshot.store');
