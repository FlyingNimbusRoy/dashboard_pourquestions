<?php

namespace App\Services\Insights;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** Cached game-activity aggregates. */
class GameInsights
{
    private const TTL = 600;

    /** Games per calendar month for the last $months months (zeros included). @return array<string,int> 'Y-m' => games */
    public function perMonth(int $months = 12): array
    {
        return Cache::store('file')->remember("insights.games.month.v1.$months", self::TTL, function () use ($months): array {
            $start = now()->startOfMonth()->subMonths($months - 1);
            $rows = DB::table('games')->where('created_at', '>=', $start)
                ->selectRaw("date_format(created_at, '%Y-%m') m, count(*) c")->groupBy('m')->pluck('c', 'm');
            $out = [];
            for ($i = 0; $i < $months; $i++) {
                $k = $start->copy()->addMonths($i)->format('Y-m');
                $out[$k] = (int) ($rows[$k] ?? 0);
            }

            return $out;
        });
    }

    /** @return array{total:int, with_list:int, without_list:int, avg_questions:float, avg_length:float} */
    public function summary(): array
    {
        return Cache::store('file')->remember('insights.games.summary.v1', self::TTL, function (): array {
            $r = DB::selectOne('select count(*) total, sum(question_array is not null) with_list,
                avg(case when question_array is not null then JSON_LENGTH(question_array) end) avg_q, avg(game_length) avg_len from games');

            return ['total' => (int) $r->total, 'with_list' => (int) $r->with_list, 'without_list' => (int) $r->total - (int) $r->with_list,
                'avg_questions' => round((float) $r->avg_q, 1), 'avg_length' => round((float) $r->avg_len, 1)];
        });
    }

    /** Questions per game in buckets of 25. @return array<string,int> */
    public function questionsPerGame(): array
    {
        return Cache::store('file')->remember('insights.games.qpg.v1', self::TTL, function (): array {
            $labels = ['1-25', '26-50', '51-75', '76-100', '101-125', '126+'];
            $out = array_fill_keys($labels, 0);
            foreach (DB::select('select JSON_LENGTH(question_array) n from games where question_array is not null and JSON_VALID(question_array)') as $r) {
                $out[$labels[min(5, intdiv(max((int) $r->n, 1) - 1, 25))]]++;
            }

            return $out;
        });
    }

    /** @return array<string,int> game_length => games */
    public function lengths(): array
    {
        return Cache::store('file')->remember('insights.games.length.v1', self::TTL, fn (): array => DB::table('games')
            ->selectRaw('game_length l, count(*) c')->groupBy('l')->orderBy('l')->pluck('c', 'l')->map(fn ($c) => (int) $c)->all());
    }
}
