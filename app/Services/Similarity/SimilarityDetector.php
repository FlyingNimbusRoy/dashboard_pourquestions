<?php

namespace App\Services\Similarity;

use App\Enums\SimilarityStatus;
use App\Models\QuestionSimilarity;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Finds similar question pairs and stores them for review in dash_question_similarities.
 *
 * Incremental by default: only questions changed since their `last_compared_at` are compared
 * (against the whole corpus). A pair a reviewer dismissed stays dismissed until one of its
 * questions is edited again.
 */
class SimilarityDetector
{
    private const CORPUS_CACHE_KEY = 'similarity.corpus';

    public function __construct(
        private readonly TextAnalyzer $analyzer,
        private readonly SimilarityScorer $scorer,
    ) {}

    /**
     * @return array{compared: int, found: int, created: int, reopened: int, removed: int}
     */
    public function run(bool $full = false): array
    {
        $startedAt = now();
        $questions = $this->loadQuestions();
        $corpus = $this->buildCorpus($questions);

        $dirty = $full
            ? array_keys($questions)
            : array_keys(array_filter($questions, fn (array $q) => $q['last_compared_at'] === null || $q['updated_at'] > $q['last_compared_at']));

        $threshold = (float) config('dashboard.similarity.store_threshold');
        $limit = (int) config('dashboard.similarity.max_candidates');
        $pairs = [];

        foreach ($dirty as $id) {
            $fingerprint = $corpus->get($id);
            foreach ($corpus->candidates($fingerprint, $limit) as $otherId) {
                $key = min($id, $otherId).'-'.max($id, $otherId);
                if (isset($pairs[$key])) {
                    continue;
                }
                $result = $this->scorer->score($fingerprint, $corpus->get($otherId), $corpus);
                if ($result['score'] >= $threshold) {
                    $pairs[$key] = $result + ['question_id' => min($id, $otherId), 'similar_question_id' => max($id, $otherId)];
                }
            }
        }

        $stats = $this->persist($pairs, $dirty, $questions);

        foreach (array_chunk($dirty, 1000) as $chunk) {
            // Query builder update: Eloquent would bump updated_at and make the question dirty again.
            DB::table('questions')->whereIn('id', $chunk)->update(['last_compared_at' => $startedAt]);
        }

        $this->forgetCorpus();

        return ['compared' => count($dirty), 'found' => count($pairs)] + $stats;
    }

    /**
     * Live "possible duplicates" check for a question that is being written or edited.
     *
     * @param  list<array{answer: string, is_correct: bool}>  $answers
     * @return list<array{question_id: int, score: float, signals: array<string, mixed>}>
     */
    public function findSimilarTo(string $text, array $answers = [], ?int $excludeId = null, int $limit = 5): array
    {
        if (trim($text) === '') {
            return [];
        }

        $corpus = Cache::store('file')->remember(
            self::CORPUS_CACHE_KEY,
            (int) config('dashboard.similarity.corpus_cache_seconds'),
            fn () => $this->buildCorpus($this->loadQuestions()),
        );

        $probe = Fingerprint::make($this->analyzer, 0, $text, $answers);
        $threshold = (float) config('dashboard.similarity.store_threshold');
        $matches = [];

        foreach ($corpus->candidates($probe, (int) config('dashboard.similarity.max_candidates')) as $id) {
            if ($id === $excludeId) {
                continue;
            }
            $result = $this->scorer->score($probe, $corpus->get($id), $corpus);
            if ($result['score'] >= $threshold) {
                $matches[] = ['question_id' => $id] + $result;
            }
        }

        usort($matches, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        return array_slice($matches, 0, $limit);
    }

    public function forgetCorpus(): void
    {
        Cache::store('file')->forget(self::CORPUS_CACHE_KEY);
    }

    /**
     * @return array<int, array{id: int, vraag: string, updated_at: ?string, last_compared_at: ?string, answers: list<array{answer: string, is_correct: bool}>}>
     */
    private function loadQuestions(): array
    {
        $questions = [];

        foreach (DB::table('questions')->select('id', 'vraag', 'updated_at', 'last_compared_at')->orderBy('id')->cursor() as $row) {
            $questions[$row->id] = [
                'id' => (int) $row->id,
                'vraag' => (string) $row->vraag,
                'updated_at' => $row->updated_at,
                'last_compared_at' => $row->last_compared_at,
                'answers' => [],
            ];
        }

        foreach (DB::table('answers')->select('question_id', 'answer', 'is_correct')->cursor() as $row) {
            if (isset($questions[$row->question_id])) {
                $questions[$row->question_id]['answers'][] = ['answer' => (string) $row->answer, 'is_correct' => (bool) $row->is_correct];
            }
        }

        return $questions;
    }

    /** @param  array<int, array{id: int, vraag: string, answers: list<array{answer: string, is_correct: bool}>}>  $questions */
    private function buildCorpus(array $questions): Corpus
    {
        $fingerprints = (function () use ($questions) {
            foreach ($questions as $q) {
                yield Fingerprint::make($this->analyzer, $q['id'], $q['vraag'], $q['answers']);
            }
        })();

        return new Corpus($fingerprints, (float) config('dashboard.similarity.max_document_frequency'));
    }

    /**
     * @param  array<string, array{question_id: int, similar_question_id: int, score: float, signals: array<string, mixed>}>  $pairs
     * @param  list<int>  $dirty
     * @param  array<int, array{updated_at: ?string}>  $questions
     * @return array{created: int, reopened: int, removed: int}
     */
    private function persist(array $pairs, array $dirty, array $questions): array
    {
        $stats = ['created' => 0, 'reopened' => 0, 'removed' => 0];
        if ($dirty === []) {
            return $stats;
        }

        $existing = collect();
        foreach (array_chunk($dirty, 1000) as $chunk) {
            $existing = $existing->merge(
                QuestionSimilarity::query()->whereIn('question_id', $chunk)->orWhereIn('similar_question_id', $chunk)->get()
            );
        }
        $existing = $existing->keyBy(fn (QuestionSimilarity $pair) => $pair->question_id.'-'.$pair->similar_question_id);

        DB::transaction(function () use ($pairs, $existing, $questions, &$stats) {
            foreach ($pairs as $key => $pair) {
                $current = $existing->get($key);

                if ($current === null) {
                    QuestionSimilarity::create($pair + ['status' => SimilarityStatus::Open]);
                    $stats['created']++;

                    continue;
                }

                $current->fill(['score' => $pair['score'], 'signals' => $pair['signals']]);

                if ($current->status !== SimilarityStatus::Open && $this->editedSinceReview($current, $questions)) {
                    $current->fill(['status' => SimilarityStatus::Open, 'reviewed_by' => null, 'reviewed_at' => null]);
                    $stats['reopened']++;
                }

                $current->save();
            }

            // Open pairs that no longer reach the threshold after an edit are no longer relevant.
            $stale = $existing->filter(fn (QuestionSimilarity $pair, string $key) => ! isset($pairs[$key]) && $pair->status === SimilarityStatus::Open);
            $stats['removed'] = $stale->count();
            QuestionSimilarity::query()->whereKey($stale->map->getKey()->values()->all())->delete();
        });

        return $stats;
    }

    /** @param  array<int, array{updated_at: ?string}>  $questions */
    private function editedSinceReview(QuestionSimilarity $pair, array $questions): bool
    {
        if ($pair->reviewed_at === null) {
            return true;
        }

        foreach ([$pair->question_id, $pair->similar_question_id] as $id) {
            $updatedAt = $questions[$id]['updated_at'] ?? null;
            if ($updatedAt !== null && Carbon::parse($updatedAt)->gt($pair->reviewed_at)) {
                return true;
            }
        }

        return false;
    }
}
