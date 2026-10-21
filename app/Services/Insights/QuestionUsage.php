<?php

namespace App\Services\Insights;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * How often each question was asked, derived from `games.question_array` (JSON list of question ids).
 * One JSON_TABLE query, cached; ids of deleted questions are ignored (counted separately as orphans).
 */
class QuestionUsage
{
    private const KEY = 'insights.usage.v1';

    private const TTL = 600;

    /** @var array<string, mixed>|null */
    private ?array $memo = null;

    public static function forget(): void
    {
        Cache::store('file')->forget(self::KEY);
    }

    /** @return array{asks: array<int, array{n:int, game:int, at:?string}>, orphans:int, questions: list<array{id:int,c:?int,g:?int,d:int}>} */
    private function data(): array
    {
        return $this->memo ??= Cache::store('file')->remember(self::KEY, self::TTL, function (): array {
            $from = "from games g join JSON_TABLE(g.question_array, '\$[*]' COLUMNS (qid INT PATH '\$')) jt on true
                     where g.question_array is not null and JSON_VALID(g.question_array)";
            $asks = [];
            foreach (DB::select("select jt.qid id, count(*) n, max(g.id) game, max(g.created_at) at
                                 $from and jt.qid in (select id from questions) group by jt.qid") as $r) {
                $asks[(int) $r->id] = ['n' => (int) $r->n, 'game' => (int) $r->game, 'at' => $r->at];
            }
            $orphans = (int) DB::selectOne("select count(distinct jt.qid) c $from and jt.qid not in (select id from questions)")->c;
            $questions = DB::table('questions')->get(['id', 'category_id', 'gamepack_id', 'difficulty'])
                ->map(fn ($q) => ['id' => (int) $q->id, 'c' => $q->category_id ? (int) $q->category_id : null,
                    'g' => $q->gamepack_id ? (int) $q->gamepack_id : null, 'd' => (int) $q->difficulty])->all();

            return compact('asks', 'orphans', 'questions');
        });
    }

    /** @return array<int, int> question id => times asked (only asked questions) */
    public function counts(): array
    {
        return array_map(fn ($a) => $a['n'], $this->data()['asks']);
    }

    public function timesAsked(int $questionId): int
    {
        return $this->data()['asks'][$questionId]['n'] ?? 0;
    }

    public function lastAskedAt(int $questionId): ?Carbon
    {
        $at = $this->data()['asks'][$questionId]['at'] ?? null;

        return $at ? Carbon::parse($at) : null;
    }

    public function lastGameId(int $questionId): ?int
    {
        return $this->data()['asks'][$questionId]['game'] ?? null;
    }

    /** @return list<int> */
    public function neverAskedIds(): array
    {
        $asked = $this->data()['asks'];

        return array_values(array_filter(array_column($this->data()['questions'], 'id'), fn ($id) => ! isset($asked[$id])));
    }

    public function totalQuestions(): int
    {
        return count($this->data()['questions']);
    }

    public function distinctAsked(): int
    {
        return count($this->data()['asks']);
    }

    public function totalAsks(): int
    {
        return array_sum($this->counts());
    }

    /** Distinct ids present in games but no longer in `questions`. */
    public function orphanIds(): int
    {
        return $this->data()['orphans'];
    }

    /** @return list<array{id:int, asks:int}> */
    public function top(int $n = 25): array
    {
        $counts = $this->counts();
        uksort($counts, fn ($a, $b) => [$counts[$b], $a] <=> [$counts[$a], $b]);

        return array_map(fn ($id) => ['id' => $id, 'asks' => $counts[$id]], array_slice(array_keys($counts), 0, $n));
    }

    /** @return array<string, int> bucket label => questions (sums to the number of questions) */
    public function histogram(): array
    {
        $buckets = ['0' => 0, '1' => 0, '2-3' => 0, '4-6' => 0, '7+' => 0];
        $asked = $this->counts();
        foreach ($this->data()['questions'] as $q) {
            $n = $asked[$q['id']] ?? 0;
            $buckets[match (true) { $n === 0 => '0', $n === 1 => '1', $n <= 3 => '2-3', $n <= 6 => '4-6', default => '7+' }]++;
        }

        return $buckets;
    }

    /** @return list<array{id:?int, name:string, questions:int, asked:int, asks:int, pct:float, avg:float}> */
    public function perCategory(): array
    {
        return $this->group('c', DB::table('categories')->pluck('name', 'id')->all(), 'No category');
    }

    /** @return list<array{id:?int, name:string, questions:int, asked:int, asks:int, pct:float, avg:float}> */
    public function perGamepack(): array
    {
        return $this->group('g', DB::table('gamepacks')->pluck('name', 'id')->all(), 'No gamepack');
    }

    /** @return list<array{id:?int, name:string, questions:int, asked:int, asks:int, pct:float, avg:float}> */
    public function perDifficulty(): array
    {
        return $this->group('d', collect(range(1, 5))->mapWithKeys(fn ($l) => [$l => (string) $l])->all(), 'Other');
    }

    /** @return array{id:?int, name:string, questions:int, asked:int, asks:int, pct:float, avg:float}|null */
    public function forGamepack(int $id): ?array
    {
        foreach ($this->perGamepack() as $row) {
            if ($row['id'] === $id) {
                return $row;
            }
        }

        return null;
    }

    /** @param array<int, string> $names */
    private function group(string $key, array $names, string $noneLabel): array
    {
        $asked = $this->counts();
        $rows = [];
        foreach ($this->data()['questions'] as $q) {
            $k = $q[$key] ?? 0;
            $rows[$k] ??= ['id' => $q[$key], 'name' => $q[$key] === null ? $noneLabel : ($names[$q[$key]] ?? "#{$q[$key]}"), 'questions' => 0, 'asked' => 0, 'asks' => 0];
            $rows[$k]['questions']++;
            if (isset($asked[$q['id']])) {
                $rows[$k]['asked']++;
                $rows[$k]['asks'] += $asked[$q['id']];
            }
        }
        foreach ($names as $id => $name) { // include empty groups
            $rows[$id] ??= ['id' => (int) $id, 'name' => $name, 'questions' => 0, 'asked' => 0, 'asks' => 0];
        }
        $rows = array_map(fn ($r) => $r + [
            'pct' => $r['questions'] ? round($r['asked'] / $r['questions'] * 100, 1) : 0.0,
            'avg' => $r['questions'] ? round($r['asks'] / $r['questions'], 2) : 0.0,
        ], array_values($rows));
        usort($rows, fn ($a, $b) => $b['questions'] <=> $a['questions']);

        return $rows;
    }
}
