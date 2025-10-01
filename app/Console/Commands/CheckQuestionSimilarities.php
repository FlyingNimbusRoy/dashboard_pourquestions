<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;
use App\Models\QuestionSimilarity;
use FuzzyWuzzy\Fuzz;

class CheckQuestionSimilarities extends Command
{
    protected $signature = 'questions:check-similarities
        {--threshold=0.8 : Minimum similarity score required (0-1)}
        {--limit=0 : Optional limit on number of questions to process (0 = all)}';

    protected $description = 'Check for similar questions and store them in question_similarities table';

    public function handle()
    {
        $threshold = (float) $this->option('threshold');
        $limit     = (int) $this->option('limit');

        $this->info("🔍 Fetching questions...");

        $questions = $limit > 0
            ? Question::limit($limit)->get()
            : Question::all();

        $count = $questions->count();
        if ($count < 2) {
            $this->warn("Not enough questions to compare.");
            return Command::SUCCESS;
        }

        $this->info("Comparing {$count} questions with threshold {$threshold}...");

        // Preload already-known similarities
        $existing = QuestionSimilarity::get()
            ->map(fn ($s) => "{$s->question_id}-{$s->similar_question_id}")
            ->flip();

        $this->info("Loaded " . count($existing) . " existing similarities.");

        $progress = $this->output->createProgressBar(($count * ($count - 1)) / 2);
        $progress->start();

        $toInsert = [];
        $fuzz = new Fuzz();

        foreach ($questions as $i => $q1) {
            $text1 = $this->normalize($q1->vraag);

            for ($j = $i + 1; $j < $count; $j++) {
                $progress->advance();

                $q2 = $questions[$j];
                $text2 = $this->normalize($q2->vraag);

                // Skip empty/very short strings
                if (strlen($text1) < 3 || strlen($text2) < 3) {
                    continue;
                }

                $key = "{$q1->id}-{$q2->id}";
                if (isset($existing[$key])) {
                    continue;
                }

                $score = $this->similarity($text1, $text2, $fuzz);

                if ($score >= $threshold) {
                    $toInsert[] = [
                        'question_id'          => $q1->id,
                        'similar_question_id'  => $q2->id,
                        'similarity_score'     => $score,
                        'handled'              => false,
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];
                }

                // Batch insert every 500 matches
                if (count($toInsert) >= 500) {
                    QuestionSimilarity::insert($toInsert);
                    $toInsert = [];
                }
            }
        }

        // Insert remaining
        if (!empty($toInsert)) {
            QuestionSimilarity::insert($toInsert);
        }

        $progress->finish();
        $this->newLine(2);
        $this->info("✅ Similarity check complete!");
        return Command::SUCCESS;
    }

    /**
     * Normalize text (lowercase, strip punctuation, collapse spaces)
     */
    protected function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text); // keep only letters/numbers
        $text = preg_replace('/\s+/', ' ', $text);               // collapse spaces
        return trim($text);
    }

    /**
     * Compute similarity score using FuzzyWuzzy + traditional metrics.
     */
    protected function similarity(string $a, string $b, Fuzz $fuzz): float
    {
        // FuzzyWuzzy scorers
        $ratio     = $fuzz->ratio($a, $b) / 100;
        $partial   = $fuzz->partialRatio($a, $b) / 100;
        $tokenSort = $fuzz->tokenSortRatio($a, $b) / 100;
        $tokenSet  = $fuzz->tokenSetRatio($a, $b) / 100;

        // Traditional scorers
        $lev = 1 - (levenshtein($a, $b) / max(strlen($a), strlen($b)));
        similar_text($a, $b, $percent);
        $sim = $percent / 100;

        // Weighted blend (you can tweak these weights)
        return (
            ($ratio * 0.2) +
            ($partial * 0.2) +
            ($tokenSort * 0.2) +
            ($tokenSet * 0.2) +
            ($lev * 0.1) +
            ($sim * 0.1)
        );
    }
}
