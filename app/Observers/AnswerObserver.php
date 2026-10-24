<?php

namespace App\Observers;

use App\Models\Answer;
use Illuminate\Support\Facades\DB;

class AnswerObserver
{
    public function created(Answer $answer): void
    {
        QuestionObserver::log($answer->question_id, 'answer_added', $answer->only(['id', 'answer', 'is_correct']));
        DB::table('questions')->where('id', $answer->question_id)->increment('version');
    }

    public function updated(Answer $answer): void
    {
        $changes = [];
        foreach (array_intersect(array_keys($answer->getChanges()), ['answer', 'is_correct']) as $field) {
            $changes[$field] = ['old' => $answer->getOriginal($field), 'new' => $answer->getAttribute($field)];
        }

        if ($changes !== []) {
            QuestionObserver::log($answer->question_id, 'answer_updated', ['id' => $answer->id] + $changes);
            DB::table('questions')->where('id', $answer->question_id)->increment('version');
        }
    }

    public function deleted(Answer $answer): void
    {
        QuestionObserver::log($answer->question_id, 'answer_deleted', $answer->only(['id', 'answer', 'is_correct']));
        DB::table('questions')->where('id', $answer->question_id)->increment('version');
    }
}
