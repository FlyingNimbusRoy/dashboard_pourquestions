<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Dashboard\QuestionController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\GamepackController;
use App\Http\Controllers\Dashboard\CharacterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('questions')->name('questions.')->group(function () {
        Route::get('/', [QuestionController::class, 'index'])->name('index');
        Route::get('/create', [QuestionController::class, 'create'])->name('create');
        Route::post('/', [QuestionController::class, 'store'])->name('store');
        Route::get('/{question}/edit', [QuestionController::class, 'edit'])->name('edit');
        Route::put('/{question}', [QuestionController::class, 'update'])->name('update');
        Route::delete('/{question}', [QuestionController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        // Custom upload routes first
        Route::get('characters/upload', [CharacterController::class, 'uploadForm'])->name('characters.upload');
        Route::post('characters/upload', [CharacterController::class, 'uploadStore'])->name('characters.upload.store');
        Route::delete('characters/upload/{filename}', [CharacterController::class, 'uploadDestroy'])->name('characters.upload.destroy');

        // Then the resource
        Route::resource('categories', CategoryController::class);
        Route::resource('gamepacks', GamepackController::class);
        Route::resource('characters', CharacterController::class);
    });
});

require __DIR__.'/auth.php';
