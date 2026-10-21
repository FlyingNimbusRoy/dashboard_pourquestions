<?php

namespace App\Services\Similarity;

/** The precomputed features of one question, built once per run. */
final class Fingerprint
{
    /**
     * @param  string  $textKey  sorted content stems joined; equal for (re)worded identical questions
     * @param  array<string, int>  $stems  stem => term frequency
     * @param  array<string, true>  $trigrams
     * @param  list<string>  $numbers
     * @param  list<string>  $correctAnswers  normalized correct answers
     * @param  array<string, true>  $allAnswers  normalized answer options
     */
    public function __construct(
        public readonly int $id,
        public readonly string $textKey,
        public readonly array $stems,
        public readonly array $trigrams,
        public readonly array $numbers,
        public readonly bool $negated,
        public readonly array $correctAnswers,
        public readonly array $allAnswers,
    ) {}

    /**
     * @param  list<array{answer: string, is_correct: bool}>  $answers
     */
    public static function make(TextAnalyzer $analyzer, int $id, string $text, array $answers = []): self
    {
        $correct = [];
        $all = [];

        foreach ($answers as $answer) {
            $normalized = $analyzer->normalize($answer['answer']);
            if ($normalized === '') {
                continue;
            }
            $all[$normalized] = true;
            if ($answer['is_correct']) {
                $correct[] = $normalized;
            }
        }

        return new self(
            id: $id,
            textKey: self::textKey($analyzer, $text),
            stems: $analyzer->stems($text),
            trigrams: $analyzer->trigrams($text),
            numbers: $analyzer->numbers($text),
            negated: $analyzer->hasNegation($text),
            correctAnswers: array_values(array_unique($correct)),
            allAnswers: $all,
        );
    }

    private static function textKey(TextAnalyzer $analyzer, string $text): string
    {
        $stems = array_keys($analyzer->stems($text));
        sort($stems);

        // A negation flips the meaning, so it is part of the identity of the text.
        return implode(' ', $stems).($analyzer->hasNegation($text) ? ' !' : '');
    }
}
