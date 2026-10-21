<?php

namespace Tests\Unit\Similarity;

use App\Services\Similarity\Corpus;
use App\Services\Similarity\Fingerprint;
use App\Services\Similarity\SimilarityScorer;
use App\Services\Similarity\TextAnalyzer;
use Tests\TestCase;

/**
 * Scores are checked against a small English background corpus, so IDF weights behave like
 * they do on real data (common words weigh little, names and facts weigh a lot).
 */
class SimilarityScorerTest extends TestCase
{
    private const BACKGROUND = [
        ['What is the capital of Germany?', 'Berlin'],
        ['What is the capital of Spain?', 'Madrid'],
        ['What is the capital of Italy?', 'Rome'],
        ['How many legs does a spider have?', '8'],
        ['Which colour is not on the flag of Ireland?', 'Red'],
        ['Who wrote The Diary of a Young Girl?', 'Anne Frank'],
        ['Which country won the football World Cup in 2010?', 'Spain'],
        ['Is a tomato a vegetable?', 'No'],
        ['Is a whale a fish?', 'No'],
        ['Is Amsterdam the capital of the Netherlands?', 'Yes'],
        ['Is The Hague the capital of the Netherlands?', 'No'],
        ['Is a peanut a nut?', 'No'],
        ['Can an ostrich fly?', 'No'],
        ['Is gold heavier than silver?', 'Yes'],
        ['Is the moon bigger than Pluto?', 'Yes'],
    ];

    private TextAnalyzer $analyzer;

    private SimilarityScorer $scorer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyzer = app(TextAnalyzer::class);
        $this->scorer = new SimilarityScorer;
    }

    public function test_reworded_question_with_same_answer_is_a_likely_duplicate(): void
    {
        $score = $this->score(['What is the capital of France?', 'Paris'], ['Which city is the capital of France?', 'Paris']);

        $this->assertGreaterThanOrEqual(config('dashboard.similarity.duplicate_threshold'), $score);
    }

    public function test_differently_phrased_question_about_the_same_fact_is_found(): void
    {
        $score = $this->score(['Who painted The Night Watch?', 'Rembrandt'], ['Which painter made The Night Watch?', 'Rembrandt']);

        $this->assertGreaterThanOrEqual(0.7, $score);
    }

    public function test_same_template_about_a_different_subject_is_not_stored(): void
    {
        $score = $this->score(['What is the capital of France?', 'Paris'], ['What is the capital of Spain?', 'Madrid']);

        $this->assertLessThan(config('dashboard.similarity.store_threshold'), $score);
    }

    public function test_a_common_answer_is_no_evidence_of_a_duplicate(): void
    {
        $common = $this->score(['Is a tomato a vegetable?', 'No'], ['Is a whale a fish?', 'No']);

        $this->assertLessThan(config('dashboard.similarity.store_threshold'), $common);
    }

    public function test_different_years_lower_the_score(): void
    {
        $sameYear = $this->score(['Which club won the Champions League in 1995?', 'Ajax'], ['Which football club won the Champions League in 1995?', 'Ajax']);
        $otherYear = $this->score(['Which club won the Champions League in 1995?', 'Ajax'], ['Which club won the European Cup in 1972?', 'Ajax'], $signals);

        $this->assertGreaterThan($otherYear, $sameYear);
        $this->assertTrue($signals['number_mismatch'] ?? false);
    }

    public function test_negation_mismatch_is_flagged(): void
    {
        $this->score(['Which country borders the Netherlands?', 'Belgium'], ['Which country does not border the Netherlands?', 'France'], $signals);

        $this->assertTrue($signals['negation_mismatch'] ?? false);
    }

    public function test_compound_written_apart_still_matches(): void
    {
        $score = $this->score(['What is the oldest footballclub in the Netherlands?', 'HFC'], ['What is the oldest football club in the Netherlands?', 'HFC']);

        $this->assertGreaterThanOrEqual(config('dashboard.similarity.duplicate_threshold'), $score);
    }

    public function test_template_question_with_different_answers_is_not_similar(): void
    {
        $fingerprints = [
            Fingerprint::make($this->analyzer, 1, 'Which song is the oldest?', [['answer' => 'Yesterday', 'is_correct' => true], ['answer' => 'Help', 'is_correct' => false]]),
            Fingerprint::make($this->analyzer, 2, 'Which song is the oldest?', [['answer' => 'Thriller', 'is_correct' => true], ['answer' => 'Beat It', 'is_correct' => false]]),
            Fingerprint::make($this->analyzer, 3, 'Which song is the oldest?', [['answer' => 'Yesterday', 'is_correct' => true], ['answer' => 'Let It Be', 'is_correct' => false]]),
        ];
        $corpus = new Corpus($fingerprints);

        $different = $this->scorer->score($corpus->get(1), $corpus->get(2), $corpus);
        $same = $this->scorer->score($corpus->get(1), $corpus->get(3), $corpus);

        $this->assertTrue($different['signals']['template_question']);
        $this->assertLessThan(config('dashboard.similarity.store_threshold'), $different['score']);
        $this->assertGreaterThanOrEqual(config('dashboard.similarity.duplicate_threshold'), $same['score']);
    }

    public function test_candidates_exclude_unrelated_questions(): void
    {
        $corpus = $this->corpus(['Who painted The Night Watch?', 'Rembrandt'], ['How many legs does an insect have?', '6']);

        $this->assertNotContains(1001, $corpus->candidates($corpus->get(1000), 50));
    }

    /**
     * @param  array{0: string, 1: string}  $a
     * @param  array{0: string, 1: string}  $b
     */
    private function score(array $a, array $b, ?array &$signals = null): float
    {
        $corpus = $this->corpus($a, $b);
        $result = $this->scorer->score($corpus->get(1000), $corpus->get(1001), $corpus);
        $signals = $result['signals'];

        return $result['score'];
    }

    /**
     * @param  array{0: string, 1: string}  ...$pair
     */
    private function corpus(array ...$pair): Corpus
    {
        $fingerprints = [];
        foreach ([...self::BACKGROUND, ...$pair] as $i => [$text, $answer]) {
            $id = $i >= count(self::BACKGROUND) ? 1000 + $i - count(self::BACKGROUND) : $i + 1;
            $fingerprints[] = Fingerprint::make($this->analyzer, $id, $text, [
                ['answer' => $answer, 'is_correct' => true],
                ['answer' => 'Something else', 'is_correct' => false],
            ]);
        }

        return new Corpus($fingerprints);
    }
}
