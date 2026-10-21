<?php

namespace App\Services\Questions;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

/**
 * Moves questions between categories and gamepacks. Every question is saved through
 * Eloquent so each move is recorded in the audit trail (dash_question_changes).
 */
class QuestionMover
{
    /**
     * @param  iterable<Question>  $questions
     * @return int number of questions that actually moved
     */
    public function moveToCategory(iterable $questions, ?int $categoryId): int
    {
        return $this->move($questions, 'category_id', $categoryId);
    }

    /**
     * @param  iterable<Question>  $questions
     * @return int number of questions that actually moved
     */
    public function moveToGamepack(iterable $questions, ?int $gamepackId): int
    {
        return $this->move($questions, 'gamepack_id', $gamepackId);
    }

    /** Moves every question of one category into another; the emptied category is kept. */
    public function mergeCategory(Category $from, Category $into): int
    {
        if ($from->is($into)) {
            return 0;
        }

        $moved = 0;
        $from->questions()->chunkById(500, function ($questions) use ($into, &$moved) {
            $moved += $this->moveToCategory($questions, $into->id);
        });

        return $moved;
    }

    /** @param  iterable<Question>  $questions */
    private function move(iterable $questions, string $column, ?int $target): int
    {
        return DB::transaction(function () use ($questions, $column, $target) {
            $moved = 0;
            foreach ($questions as $question) {
                if ($question->{$column} !== $target) {
                    $question->update([$column => $target]);
                    $moved++;
                }
            }

            return $moved;
        });
    }
}
