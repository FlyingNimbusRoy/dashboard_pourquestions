<?php

namespace App\Services\LiveSync;

use App\Models\PendingLiveChange;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** Writes pending live changes from model events. Values are read back from the database so they are raw and exact. */
class LiveChangeRecorder
{
    /** Bookkeeping columns that are never a meaningful change (and never exported on insert/update). */
    public const IGNORED = ['created_at', 'updated_at', 'last_compared_at'];

    private static bool $enabled = true;

    public static function withoutRecording(callable $callback): mixed
    {
        $prev = self::$enabled;
        self::$enabled = false;
        try {
            return $callback();
        } finally {
            self::$enabled = $prev;
        }
    }

    public static function created(Model $m): void
    {
        self::push($m, 'insert', ['row' => self::row($m)]);
    }

    public static function updated(Model $m): void
    {
        $fresh = self::row($m);
        $changes = [];
        foreach (array_diff(array_keys($m->getChanges()), self::IGNORED) as $col) {
            $old = $m->getRawOriginal($col);
            $new = $fresh[$col] ?? null;
            if ((string) $old !== (string) $new || ($old === null) !== ($new === null)) {
                $changes[$col] = [$old, $new];
            }
        }
        if ($changes !== []) {
            self::push($m, 'update', ['changes' => $changes]);
        }
    }

    public static function deleting(Model $m): void
    {
        self::push($m, 'delete', ['row' => self::row($m)]);
    }

    /** @param array<string, mixed> $extra */
    private static function push(Model $m, string $op, array $extra): void
    {
        if (! self::$enabled) {
            return;
        }
        PendingLiveChange::create([
            'operation' => $op,
            'table_name' => $m->getTable(),
            'record_id' => $m->getKey(),
            'user_id' => auth()->id(),
            'created_at' => now(),
        ] + $extra);
    }

    /** @return array<string, mixed> */
    private static function row(Model $m): array
    {
        $row = DB::table($m->getTable())->where($m->getKeyName(), $m->getKey())->first();

        return $row ? (array) $row : $m->getAttributes();
    }
}
