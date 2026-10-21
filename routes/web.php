<?php

use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

// Dashboard JSON API: session-authenticated, admins only. Each controller delegates to a CRUD service.
Route::prefix('api')->name('api.')->middleware(['auth', EnsureAdmin::class])->group(function () {
    require __DIR__.'/api/questions.php';
    require __DIR__.'/api/game.php';
});
