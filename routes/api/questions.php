<?php

use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CharacterController;
use App\Http\Controllers\Api\GamepackController;
use App\Http\Controllers\Api\ModifierController;
use App\Http\Controllers\Api\QuestionController;
use Illuminate\Support\Facades\Route;

// Question domain: questions, answers, categories, gamepacks, characters, modifiers.
Route::post('questions/move-category', [QuestionController::class, 'moveCategory'])->name('questions.move-category');
Route::post('categories/{category}/merge', [CategoryController::class, 'merge'])->name('categories.merge');

Route::apiResource('questions', QuestionController::class);
Route::apiResource('answers', AnswerController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('gamepacks', GamepackController::class);
Route::apiResource('characters', CharacterController::class);
Route::apiResource('modifiers', ModifierController::class);
