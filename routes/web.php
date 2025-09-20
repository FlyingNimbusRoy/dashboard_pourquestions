<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\QuestionController;
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\GamepackController;
use App\Http\Controllers\Dashboard\CharacterController;
use App\Http\Controllers\Dashboard\ToolsController;
use App\Http\Controllers\Dashboard\CommentController;
use App\Http\Controllers\Dashboard\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


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

        // FIXED: remove extra 'questions/' prefix in URL
        Route::get('/import', [QuestionController::class, 'importView'])->name('import.view');
        Route::post('/import', [QuestionController::class, 'importExcel'])->name('import');
        Route::get('/template', [QuestionController::class, 'downloadTemplate'])->name('template');
    });


    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        // Custom upload routes first
        Route::get('characters/upload', [CharacterController::class, 'uploadForm'])->name('characters.upload');
        Route::post('characters/upload', [CharacterController::class, 'uploadStore'])->name('characters.upload.store');
        Route::delete('characters/upload/{filename}', [CharacterController::class, 'uploadDestroy'])->name('characters.upload.destroy');

        // Tools
        Route::get('tools', [ToolsController::class, 'index'])->name('tools.index');
        Route::get('tools/grading', [ToolsController::class, 'grading'])->name('tools.grading');
        Route::post('tools/grading/{question}', [ToolsController::class, 'updateGrading'])->name('tools.grading.update');
        Route::get('tools/relevancy', [ToolsController::class, 'relevancyChecker'])->name('tools.relevancy');
        Route::post('tools/relevancy/validate', [ToolsController::class, 'validateQuestion'])->name('tools.relevancy.validate');
        Route::get('tools/category-balance', [ToolsController::class, 'categoryBalance'])->name('tools.category_balance');
        Route::get('/tools/recategorisation', [ToolsController::class, 'recategorisation'])->name('tools.recategorisation');
        Route::patch('/tools/recategorisation/{id}', [ToolsController::class, 'recategorisationUpdate'])->name('tools.recategorisation.update');

        //Users
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::patch('users/{user}/toggle-admin', [UserController::class, 'toggleAdmin'])->name('users.toggle-admin');

        Route::get('tools/similarities', [ToolsController::class, 'similarities'])->name('tools.similarities');
        Route::post('tools/similarities/{id}/handle', [ToolsController::class, 'markSimilarityHandled'])->name('tools.similarities.handle');

        // Then the resource
        Route::resource('categories', CategoryController::class);
        Route::resource('gamepacks', GamepackController::class);
        Route::resource('characters', CharacterController::class);
        Route::resource('comments', CommentController::class);
    });
});

require __DIR__.'/auth.php';
