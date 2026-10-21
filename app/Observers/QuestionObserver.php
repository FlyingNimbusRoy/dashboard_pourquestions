<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\Gamepack;
use App\Models\Question;
use App\Models\QuestionChange;

class QuestionObserver
{
    /** Bookkeeping columns that are not meaningful changes. */
    private const IGNORED = ['created_at', 'updated_at', 'last_compared_at'];

    public function updated(Question $question): void
    {
        $changes = [];

        foreach (array_diff(array_keys($question->getChanges()), self::IGNORED) as $field) {
            $change = ['old' => $question->getOriginal($field), 'new' => $question->getAttribute($field)];

            if ($field === 'category_id') {
                $change += ['old_label' => Category::find($change['old'])?->name, 'new_label' => Category::find($change['new'])?->name];
            } elseif ($field === 'gamepack_id') {
                $change += ['old_label' => Gamepack::find($change['old'])?->name, 'new_label' => Gamepack::find($change['new'])?->name];
            }

            $changes[$field] = $change;
        }

        if ($changes !== []) {
            self::log($question->id, 'updated', $changes);
        }
    }

    public function deleting(Question $question): void
    {
        self::log($question->id, 'deleted', [
            'snapshot' => $question->only(['vraag', 'trivia', 'category_id', 'gamepack_id', 'difficulty', 'is_nsfw']),
            'answers' => $question->answers()->get(['answer', 'is_correct'])->toArray(),
        ]);

        // The game schema has no cascading foreign key on answers.question_id.
        $question->answers()->delete();
    }

    /** @param  array<string, mixed>  $changes */
    public static function log(int $questionId, string $action, array $changes): void
    {
        QuestionChange::create([
            'question_id' => $questionId,
            'user_id' => auth()->id(),
            'action' => $action,
            'changes' => self::serializable($changes),
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, mixed>
     */
    private static function serializable(array $changes): array
    {
        array_walk_recursive($changes, function (&$value) {
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }
        });

        return $changes;
    }
}
