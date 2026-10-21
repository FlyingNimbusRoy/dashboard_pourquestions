<?php

namespace App\Services\Similarity;

/**
 * All question fingerprints plus the statistics and inverted indexes needed to score
 * pairs (IDF weights) and to find candidates without comparing every pair.
 */
final class Corpus
{
    /** Rarest trigrams per question used for candidate lookup. */
    private const CANDIDATE_TRIGRAMS = 8;

    /** @var array<int, Fingerprint> */
    private array $fingerprints = [];

    /** @var array<string, list<int>> */
    private array $stemPostings = [];

    /** @var array<string, list<int>> */
    private array $answerPostings = [];

    /** @var array<string, list<int>> */
    private array $trigramPostings = [];

    /** @var array<int, array<string, float>> unit-length TF-IDF vectors, built lazily */
    private array $vectors = [];

    /** @var array<string, list<int>> question ids per identical text (textKey) */
    private array $textGroups = [];

    private int $maxPostings;

    /** @param  iterable<Fingerprint>  $fingerprints */
    public function __construct(iterable $fingerprints, float $maxDocumentFrequency = 0.05)
    {
        foreach ($fingerprints as $fingerprint) {
            $this->fingerprints[$fingerprint->id] = $fingerprint;
            if ($fingerprint->textKey !== '') {
                $this->textGroups[$fingerprint->textKey][] = $fingerprint->id;
            }

            foreach ($fingerprint->stems as $stem => $tf) {
                $this->stemPostings[$stem][] = $fingerprint->id;
            }
            foreach ($fingerprint->correctAnswers as $answer) {
                $this->answerPostings[$answer][] = $fingerprint->id;
            }
            foreach ($fingerprint->trigrams as $trigram => $true) {
                $this->trigramPostings[$trigram][] = $fingerprint->id;
            }
        }

        // Features shared by more questions than this ("ja", "nederland") are useless for
        // finding candidates; small corpora get a floor so tests and fresh installs still work.
        $this->maxPostings = max(25, (int) ceil(count($this->fingerprints) * $maxDocumentFrequency));
    }

    public function count(): int
    {
        return count($this->fingerprints);
    }

    public function get(int $id): ?Fingerprint
    {
        return $this->fingerprints[$id] ?? null;
    }

    /** Number of questions with this (normalized) correct answer. */
    public function answerFrequency(string $answer): int
    {
        return count($this->answerPostings[$answer] ?? []);
    }

    /** How many questions have this exact (stemmed) text; template questions occur many times. */
    public function textFrequency(Fingerprint $fingerprint): int
    {
        return count($this->textGroups[$fingerprint->textKey] ?? []);
    }

    public function idf(string $stem): float
    {
        $df = isset($this->stemPostings[$stem]) ? count($this->stemPostings[$stem]) : 0;

        return log((count($this->fingerprints) + 1) / ($df + 1)) + 1;
    }

    /** @return array<string, float> */
    public function vector(Fingerprint $fingerprint): array
    {
        if (isset($this->vectors[$fingerprint->id]) && $this->fingerprints[$fingerprint->id] === $fingerprint) {
            return $this->vectors[$fingerprint->id];
        }

        $vector = [];
        $norm = 0.0;
        foreach ($fingerprint->stems as $stem => $tf) {
            $weight = (1 + log($tf)) * $this->idf($stem);
            $vector[$stem] = $weight;
            $norm += $weight * $weight;
        }

        if ($norm > 0) {
            $norm = sqrt($norm);
            foreach ($vector as $stem => $weight) {
                $vector[$stem] = $weight / $norm;
            }
        }

        if (isset($this->fingerprints[$fingerprint->id]) && $this->fingerprints[$fingerprint->id] === $fingerprint) {
            $this->vectors[$fingerprint->id] = $vector;
        }

        return $vector;
    }

    /**
     * Questions sharing a rare stem, a correct answer or several rare trigrams with the
     * given fingerprint, ranked by how many such features they share.
     *
     * @return list<int>
     */
    public function candidates(Fingerprint $fingerprint, int $limit): array
    {
        $hits = [];

        foreach ($fingerprint->stems as $stem => $tf) {
            $this->collect($hits, $this->stemPostings[$stem] ?? [], 2);
        }

        foreach ($fingerprint->correctAnswers as $answer) {
            $this->collect($hits, $this->answerPostings[$answer] ?? [], 2);
        }

        $rarest = array_keys($fingerprint->trigrams);
        usort($rarest, fn (string $a, string $b) => count($this->trigramPostings[$a] ?? []) <=> count($this->trigramPostings[$b] ?? []));
        foreach (array_slice($rarest, 0, self::CANDIDATE_TRIGRAMS) as $trigram) {
            $this->collect($hits, $this->trigramPostings[$trigram] ?? [], 1);
        }

        // Template questions share their text; judge them by their answers, so always compare the group.
        $group = $this->textGroups[$fingerprint->textKey] ?? [];
        if (count($group) > 1 && count($group) <= 50) {
            foreach ($group as $id) {
                $hits[$id] = ($hits[$id] ?? 0) + 2;
            }
        }

        unset($hits[$fingerprint->id]);

        // One shared trigram alone is noise; require a stem or answer, or two trigrams.
        $hits = array_filter($hits, fn (int $weight) => $weight >= 2);
        arsort($hits);

        return array_slice(array_keys($hits), 0, $limit);
    }

    /**
     * @param  array<int, int>  $hits
     * @param  list<int>  $postings
     */
    private function collect(array &$hits, array $postings, int $weight): void
    {
        if ($postings === [] || count($postings) > $this->maxPostings) {
            return;
        }

        foreach ($postings as $id) {
            $hits[$id] = ($hits[$id] ?? 0) + $weight;
        }
    }
}
