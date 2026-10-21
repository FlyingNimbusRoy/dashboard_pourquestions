<?php

namespace App\Services\Insights;

use App\Models\Question;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** Cached question-bank health numbers. */
class BankInsights
{
    private const TTL = 300;

    /** @return array{total:int, due:int, validated_pct:float, no_category:int, no_gamepack:int, grading_due:int} */
    public function health(): array
    {
        return Cache::store('file')->remember('insights.bank.health.v2', self::TTL, function (): array {
            $total = Question::count();
            $due = Question::validationDue()->count();

            return ['total' => $total, 'due' => $due,
                'validated_pct' => $total ? round(($total - $due) / $total * 100, 1) : 0.0,
                'no_category' => Question::whereNull('category_id')->count(),
                'no_gamepack' => Question::whereNull('gamepack_id')->count(),
                'grading_due' => Question::gradingDue()->count()];
        });
    }

    /** % of questions not due for review, per category (ascending). @return list<array{name:string, questions:int, valid:int, pct:float}> */
    public function validationByCategory(): array
    {
        return Cache::store('file')->remember('insights.bank.validation.v1', self::TTL, function (): array {
            $cutoff = now()->subDays((int) config('dashboard.validation.revalidate_after_days'));
            $rows = DB::table('questions as q')->leftJoin('categories as c', 'c.id', '=', 'q.category_id')
                ->selectRaw('coalesce(c.name, ?) name, count(*) questions, sum(q.last_validated >= ?) valid', ['No category', $cutoff])
                ->groupBy('c.id', 'c.name')->get();

            return $rows->map(fn ($r) => ['name' => $r->name, 'questions' => (int) $r->questions, 'valid' => (int) $r->valid,
                'pct' => round($r->valid / max(1, $r->questions) * 100, 1)])->sortBy('pct')->values()->all();
        });
    }
}
