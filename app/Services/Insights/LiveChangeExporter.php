<?php

namespace App\Services\Insights;

use App\Models\LiveExportBatch;
use App\Models\PendingLiveChange;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Builds a reviewable SQL file from pending live changes. Never touches the live connection. */
class LiveChangeExporter
{
    /** Columns that identify a row on live when deleting (besides id). */
    private const DELETE_GUARD = [
        'questions' => ['vraag'],
        'answers' => ['question_id', 'answer'],
        'categories' => ['name'],
        'gamepacks' => ['name'],
        'characters' => ['name'],
        'modifiers' => ['name'],
        'achievements' => ['name'],
        'games' => ['name'],
        'gamepack_purchased' => ['gamepack_id', 'user_id'],
    ];

    /** Foreign key column => parent table, so children are only written when their parent insert really happened. */
    private const PARENTS = [
        'answers' => ['question_id' => 'questions'],
        'questions' => ['category_id' => 'categories', 'gamepack_id' => 'gamepacks'],
        'characters' => ['gamepack_id' => 'gamepacks', 'parent_character_id' => 'characters'],
        'modifiers' => ['coupled_gamepack_id' => 'gamepacks'],
        'achievements' => ['character_id' => 'characters'],
        'gamepack_purchased' => ['gamepack_id' => 'gamepacks'],
    ];

    /**
     * @param  list<int>|null  $onlyIds  pending change ids to export (default: all open)
     * @return array{sql:string, count:int, ids:list<int>}
     */
    public function build(?string $userName = null, ?array $onlyIds = null): array
    {
        $changes = PendingLiveChange::query()->open()->with('user')
            ->when($onlyIds !== null, fn ($q) => $q->whereIn('id', $onlyIds))
            ->orderBy('id')->get();

        $entries = $this->collapse($changes);
        $inserted = []; // "table|id" => true, for rows inserted in this file
        foreach ($entries as $e) {
            if ($e['kind'] === 'insert') {
                $inserted[$e['table'].'|'.$e['id']] = true;
            }
        }

        $counts = [];
        $body = [];
        $warnings = [];
        foreach ($entries as $e) {
            $counts[$e['table']][$e['kind']] = ($counts[$e['table']][$e['kind']] ?? 0) + 1;
            $body[] = $e['comment'];
            foreach ($this->statements($e, $inserted) as $s) {
                $body[] = $s;
            }
            if ($e['kind'] === 'insert') {
                $warnings[] = sprintf('SELECT %s AS WARNING WHERE %s = 0;',
                    $this->q("insert into {$e['table']} id {$e['id']} was SKIPPED: the id already exists on live or its parent row was skipped"),
                    $this->var($e['table'], $e['id']));
            }
        }

        $lines = [
            '-- Pending live changes exported from the local dashboard',
            '-- Generated: '.now()->toDateTimeString().' by '.($userName ?? 'console'),
            '-- Recorded changes: '.$changes->count().', statements after collapsing: '.count($entries),
        ];
        foreach ($counts as $t => $k) {
            $lines[] = "--   {$t}: ".collect($k)->map(fn ($n, $kind) => "$n $kind")->implode(', ');
        }
        $lines = array_merge($lines, [
            '-- Every statement is guarded (UPDATE/DELETE on the old values, INSERT only if the id is free), so a row that changed on live',
            '-- in the meantime is left alone and re-running the file is harmless. Check the WARNING rows returned before COMMIT.',
            '-- ID COLLISION RISK: inserts keep the local id. If live created a row with that id since the local copy was made, the insert is',
            '-- skipped (and everything depending on it, e.g. the answers of a skipped question) and a WARNING row is returned.',
            'SET NAMES utf8mb4;',
            'START TRANSACTION;',
            ...$body,
            ...$warnings,
            'COMMIT;',
        ]);

        return ['sql' => implode("\n", $lines)."\n", 'count' => $changes->count(), 'ids' => $changes->pluck('id')->all()];
    }

    /** @param  list<int>  $ids */
    public function markExported(array $ids, ?int $userId = null, ?string $filename = null, ?string $sql = null): LiveExportBatch
    {
        return DB::transaction(function () use ($ids, $userId, $filename, $sql) {
            $batch = LiveExportBatch::create([
                'user_id' => $userId, 'change_count' => count($ids), 'sql' => $sql ?? '',
                'filename' => $filename ?? 'live-changes-'.now()->format('Ymd-His').'.sql', 'exported_at' => now(),
            ]);
            PendingLiveChange::query()->whereIn('id', $ids)->update(['exported_at' => now(), 'batch_id' => $batch->id]);

            return $batch;
        });
    }

    /**
     * Merge every row's sequence of changes into one entry, ordered chronologically.
     *
     * @param  Collection<int, PendingLiveChange>  $changes
     * @return list<array<string, mixed>>
     */
    private function collapse(Collection $changes): array
    {
        $groups = [];
        foreach ($changes as $c) {
            $key = $c->table_name.'|'.$c->record_id;
            $groups[$key] ??= ['table' => $c->table_name, 'id' => (int) $c->record_id, 'kind' => null, 'row' => [], 'cols' => [], 'firstId' => $c->id, 'lastId' => $c->id, 'who' => [], 'when' => $c->created_at, 'cancelled' => false, 'deleteRow' => null];
            $g = &$groups[$key];
            $g['lastId'] = $c->id;
            $g['who'][$c->user?->name ?? 'system'] = true;
            $g['when'] = $c->created_at;
            if ($c->operation === 'insert') {
                $g['kind'] = 'insert';
                $g['row'] = $c->row ?? [];
            } elseif ($c->operation === 'update') {
                $g['kind'] ??= 'update';
                foreach ($c->changes ?? [] as $col => [$old, $new]) {
                    if ($g['kind'] === 'insert') {
                        $g['row'][$col] = $new;
                    } else {
                        $g['cols'][$col] = [array_key_exists($col, $g['cols']) ? $g['cols'][$col][0] : $old, $new];
                    }
                }
            } elseif ($g['kind'] === 'insert') { // delete of a row created locally: live never needs to know
                $g['cancelled'] = true;
            } else {
                $g['kind'] = 'delete';
                $g['deleteRow'] = $c->row ?? [];
            }
            unset($g);
        }

        $out = [];
        foreach ($groups as $g) {
            if ($g['cancelled']) {
                continue;
            }
            if ($g['kind'] === 'update') {
                $g['cols'] = array_filter($g['cols'], fn ($pair) => ! $this->same($pair[0], $pair[1]));
                if ($g['cols'] === []) {
                    continue;
                }
            }
            $g['order'] = $g['kind'] === 'insert' ? $g['firstId'] : $g['lastId'];
            $g['comment'] = sprintf('-- #%d %s %s: %s %s#%d%s', $g['order'], $g['when']?->format('Y-m-d H:i'), $this->oneLine(implode(', ', array_keys($g['who']))),
                $g['kind'], $g['table'], $g['id'], $g['kind'] === 'update' ? ' ('.implode(', ', array_keys($g['cols'])).')' : '');
            $out[] = $g;
        }
        usort($out, fn ($a, $b) => $a['order'] <=> $b['order']);

        return $out;
    }

    /**
     * @param  array<string, mixed>  $e
     * @param  array<string, bool>  $inserted
     * @return list<string>
     */
    private function statements(array $e, array $inserted): array
    {
        $t = $this->ident($e['table']);
        $id = (int) $e['id'];
        $parents = self::PARENTS[$e['table']] ?? [];

        // Parents inserted by this same file must really have been inserted (their id may have collided on live).
        $parentGuard = function (array $values) use ($parents, $inserted): string {
            $g = '';
            foreach ($parents as $col => $parentTable) {
                $v = $values[$col] ?? null;
                if ($v !== null && isset($inserted[$parentTable.'|'.$v])) {
                    $g .= ' AND '.$this->var($parentTable, (int) $v).' = 1';
                }
            }

            return $g;
        };

        if ($e['kind'] === 'insert') {
            $row = array_diff_key($e['row'], ['last_compared_at' => 1]);
            $cols = implode(', ', array_map(fn ($c) => $this->ident($c), array_keys($row)));
            $vals = implode(', ', array_map(fn ($v) => $this->lit($v), $row));

            return [
                "INSERT INTO {$t} ({$cols}) SELECT {$vals} FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM {$t} WHERE `id` = {$id})".$parentGuard($row).';',
                'SET '.$this->var($e['table'], $id).' = ROW_COUNT();',
            ];
        }

        if ($e['kind'] === 'update') {
            $set = [];
            $where = ["`id` = {$id}"];
            $new = [];
            foreach ($e['cols'] as $col => [$old, $val]) {
                $set[] = $this->ident($col).' = '.$this->lit($val);
                $where[] = $this->ident($col).' <=> '.$this->lit($old);
                $new[$col] = $val;
            }
            $set[] = '`updated_at` = NOW()';

            return ["UPDATE {$t} SET ".implode(', ', $set).' WHERE '.implode(' AND ', $where).$parentGuard($new).';'];
        }

        // delete
        $row = $e['deleteRow'];
        foreach ($e['cols'] as $col => [$old]) {
            $row[$col] = $old; // live still has the pre-edit value
        }
        $guard = '';
        foreach (self::DELETE_GUARD[$e['table']] ?? [] as $col) {
            $guard .= ' AND '.$this->ident($col).' <=> '.$this->lit($row[$col] ?? null);
        }
        $stmts = [];
        if ($e['table'] === 'questions') {
            $stmts[] = "DELETE FROM `answers` WHERE `question_id` = {$id} AND EXISTS (SELECT 1 FROM `questions` WHERE `id` = {$id}{$guard});";
        }
        $stmts[] = "DELETE FROM {$t} WHERE `id` = {$id}{$guard};";

        return $stmts;
    }

    private function same(mixed $a, mixed $b): bool
    {
        return $a === null || $b === null ? $a === $b : (string) $a === (string) $b;
    }

    private function var(string $table, int $id): string
    {
        return '@ins_'.preg_replace('/\W/', '_', $table).'_'.$id;
    }

    private function ident(string $name): string
    {
        return '`'.str_replace('`', '', $name).'`';
    }

    private function oneLine(string $s): string
    {
        return trim(preg_replace('/\s+/', ' ', $s));
    }

    private function q(string $s): string
    {
        return DB::connection()->getPdo()->quote($s);
    }

    private function lit(mixed $v): string
    {
        return match (true) {
            $v === null => 'NULL',
            is_bool($v) => $v ? '1' : '0',
            is_int($v), is_float($v) => (string) $v,
            is_array($v) => $this->q(json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            default => $this->q((string) $v),
        };
    }
}
