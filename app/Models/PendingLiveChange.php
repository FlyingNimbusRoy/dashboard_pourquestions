<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A change made on the local copy (insert, update or delete) that the owner still has to apply to the live database. */
#[Table('dash_pending_live_changes')]
#[Fillable(['operation', 'table_name', 'record_id', 'column_name', 'old_value', 'new_value', 'changes', 'row', 'user_id', 'created_at', 'exported_at', 'batch_id', 'discarded_at'])]
class PendingLiveChange extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'exported_at' => 'datetime', 'discarded_at' => 'datetime', 'changes' => 'array', 'row' => 'array'];
    }

    /** Waiting for export: not exported and not discarded. */
    public function scopeOpen(Builder $q): Builder
    {
        return $q->whereNull('exported_at')->whereNull('discarded_at');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Human readable one-line description of what changed. */
    public function summary(): string
    {
        return match ($this->operation) {
            'insert' => 'New row: '.$this->label(),
            'delete' => 'Deleted row: '.$this->label(),
            default => collect($this->changes ?? [])->map(fn ($c, $col) => "{$col}: ".self::short($c[0]).' -> '.self::short($c[1]))->implode("\n"),
        };
    }

    private function label(): string
    {
        $row = $this->row ?? [];
        foreach (['vraag', 'name', 'answer'] as $k) {
            if (isset($row[$k])) {
                return self::short($row[$k], 80);
            }
        }

        return '#'.$this->record_id;
    }

    public static function short(mixed $v, int $max = 60): string
    {
        if ($v === null) {
            return 'NULL';
        }
        $s = is_scalar($v) ? (string) $v : json_encode($v);

        return mb_strlen($s) > $max ? mb_substr($s, 0, $max).'…' : $s;
    }
}
