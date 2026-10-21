<?php

namespace App\Services\Similarity;

/**
 * Scores how likely two questions are duplicates (0..1) and explains why. The text score
 * blends word-level TF-IDF (meaning-bearing rare words) with character trigrams (typos,
 * compounds, inflections); answers and numbers then confirm or contradict it.
 */
class SimilarityScorer
{
    private const WEIGHT_COSINE = 0.6;

    private const WEIGHT_TRIGRAMS = 0.4;

    private const NEAR_IDENTICAL_CHARACTERS = 0.85;

    /** Minimum text score for two questions to count as "the same text" (template check). */
    private const TEMPLATE_MIN_TEXT = 0.9;

    /** Near-identical wording with a clearly different correct answer is a sibling, not a duplicate. */
    private const DIFFERENT_ANSWER_FACTOR = 0.7;

    /**
     * @return array{score: float, signals: array<string, mixed>}
     */
    public function score(Fingerprint $a, Fingerprint $b, Corpus $corpus): array
    {
        $cosine = $this->cosine($corpus->vector($a), $corpus->vector($b));
        $trigrams = $this->dice($a->trigrams, $b->trigrams);
        $text = self::WEIGHT_COSINE * $cosine + self::WEIGHT_TRIGRAMS * $trigrams;

        // Near-identical characters mean near-identical text even when word boundaries differ
        // (compounds written apart, typos), which the word-level score cannot see.
        if ($trigrams >= self::NEAR_IDENTICAL_CHARACTERS) {
            $text = max($text, $trigrams * 0.95);
        }

        $score = $text;
        $signals = [
            'text' => round($text, 3),
            'words' => round($cosine, 3),
            'characters' => round($trigrams, 3),
            'shared_words' => array_values(array_intersect(array_keys($a->stems), array_keys($b->stems))),
        ];

        if ($text >= self::TEMPLATE_MIN_TEXT && $this->isTemplate($a, $b, $corpus)) {
            return $this->scoreTemplate($a, $b, $text, $signals);
        }

        if ($a->correctAnswers !== [] && $b->correctAnswers !== []) {
            $answer = $this->bestAnswerMatch($a->correctAnswers, $b->correctAnswers);
            $options = $this->jaccard($a->allAnswers, $b->allAnswers);
            $signals['correct_answer'] = round($answer, 3);
            $signals['answer_options'] = round($options, 3);

            if ($answer >= 0.9) {
                // Same answer: likely the same fact asked differently, but only if the answer is
                // informative. "Ja", "Waar" or "11" are shared by many unrelated questions.
                $boost = $this->answerBoost($a, $b, $corpus);
                $signals['answer_informative'] = $boost > 0;
                $score += (1 - $score) * $boost;
                if ($boost > 0 && $options >= 0.5) {
                    $score += (1 - $score) * 0.15;
                }
            } elseif ($answer < 0.3) {
                // Similar wording but a different answer is usually a template ("capital of X").
                $score *= self::DIFFERENT_ANSWER_FACTOR;
            }
        }

        if ($a->numbers !== [] && $b->numbers !== [] && array_intersect($a->numbers, $b->numbers) === []) {
            $score *= 0.8;
            $signals['number_mismatch'] = true;
        }

        if ($a->negated !== $b->negated) {
            $score *= 0.9;
            $signals['negation_mismatch'] = true;
        }

        return ['score' => round(min(1.0, max(0.0, $score)), 4), 'signals' => $signals];
    }

    /**
     * Template questions ("Which song is the oldest?") repeat their text; the meaning is in the
     * answers. Identical text, or a text that occurs several times in the corpus, qualifies.
     */
    private function isTemplate(Fingerprint $a, Fingerprint $b, Corpus $corpus): bool
    {
        if ($a->textKey !== '' && $a->textKey === $b->textKey) {
            return true;
        }

        $min = (int) config('dashboard.similarity.template_min_occurrences', 3);

        return $corpus->textFrequency($a) >= $min || $corpus->textFrequency($b) >= $min;
    }

    /**
     * Same correct answer or largely overlapping options means the same question; otherwise the
     * pair is just two instances of one template and scores low.
     *
     * @param  array<string, mixed>  $signals
     * @return array{score: float, signals: array<string, mixed>}
     */
    private function scoreTemplate(Fingerprint $a, Fingerprint $b, float $text, array $signals): array
    {
        $signals['template_question'] = true;

        if ($a->correctAnswers === [] || $b->correctAnswers === [] || ($a->allAnswers === [] && $b->allAnswers === [])) {
            // No answers to judge by: flag it, but stay below the duplicate bucket.
            $signals['template_unjudged'] = true;

            return ['score' => round(min($text, 0.7), 4), 'signals' => $signals];
        }

        $correct = $this->bestAnswerMatch($a->correctAnswers, $b->correctAnswers);
        $options = $this->jaccard($a->allAnswers, $b->allAnswers);
        $signals['correct_answer'] = round($correct, 3);
        $signals['answer_options'] = round($options, 3);

        if ($correct >= 0.9 || $options >= (float) config('dashboard.similarity.template_options_overlap', 0.5)) {
            $signals['template_question'] = false;
            $signals['template_same_answers'] = true;

            return ['score' => round(min(1.0, 0.8 + 0.1 * $correct + 0.1 * $options), 4), 'signals' => $signals];
        }

        return ['score' => round(0.3 * max($correct, $options), 4), 'signals' => $signals];
    }

    /** The rarer the shared correct answer across all questions, the stronger the evidence. */
    private function answerBoost(Fingerprint $a, Fingerprint $b, Corpus $corpus): float
    {
        $shared = array_intersect($a->correctAnswers, $b->correctAnswers);
        if ($shared === []) {
            // Near-identical spelling ("Rembrandt" / "Rembrandt van Rijn"): treat as rare.
            return 0.3;
        }

        $frequency = min(array_map(fn (string $answer) => max(1, $corpus->answerFrequency($answer)), $shared));

        return match (true) {
            $frequency <= 3 => 0.4,
            $frequency <= 10 => 0.25,
            default => 0.0,
        };
    }

    /**
     * @param  array<string, float>  $a
     * @param  array<string, float>  $b
     */
    private function cosine(array $a, array $b): float
    {
        if (count($a) > count($b)) {
            [$a, $b] = [$b, $a];
        }

        $dot = 0.0;
        foreach ($a as $term => $weight) {
            if (isset($b[$term])) {
                $dot += $weight * $b[$term];
            }
        }

        return min(1.0, $dot);
    }

    /**
     * @param  array<string, true>  $a
     * @param  array<string, true>  $b
     */
    private function dice(array $a, array $b): float
    {
        $total = count($a) + count($b);

        return $total === 0 ? 0.0 : 2 * count(array_intersect_key($a, $b)) / $total;
    }

    /**
     * @param  array<string, true>  $a
     * @param  array<string, true>  $b
     */
    private function jaccard(array $a, array $b): float
    {
        $union = count($a + $b);

        return $union === 0 ? 0.0 : count(array_intersect_key($a, $b)) / $union;
    }

    /**
     * @param  list<string>  $a
     * @param  list<string>  $b
     */
    private function bestAnswerMatch(array $a, array $b): float
    {
        $analyzer = app(TextAnalyzer::class);
        $best = 0.0;

        foreach ($a as $left) {
            foreach ($b as $right) {
                $best = max($best, $left === $right ? 1.0 : $this->dice(
                    $analyzer->trigrams($left, contentOnly: false),
                    $analyzer->trigrams($right, contentOnly: false),
                ));
            }
        }

        return $best;
    }
}
