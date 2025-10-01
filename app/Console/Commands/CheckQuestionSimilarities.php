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
        {--limit=0 : Optional limit on number of new questions to process (0 = all)}';

    protected $description = 'Incrementally check for similar questions and store them in question_similarities table';

    public function handle()
    {
        $threshold = (float) $this->option('threshold');
        $limit     = (int) $this->option('limit');

        $this->info("🔍 Fetching unprocessed questions...");

        // Only fetch questions that have never been compared
        $questions = $limit > 0
            ? Question::whereNull('last_compared_at')->limit($limit)->get()
            : Question::whereNull('last_compared_at')->get();

        $count = $questions->count();
        if ($count === 0) {
            $this->info("✅ No new questions to compare.");
            return Command::SUCCESS;
        }

        $this->info("Comparing {$count} new questions with threshold {$threshold}...");

        $existing = QuestionSimilarity::get()
            ->map(fn ($s) => "{$s->question_id}-{$s->similar_question_id}")
            ->flip();

        $this->info("Loaded " . count($existing) . " existing similarities.");

        $progress = $this->output->createProgressBar($count);
        $progress->start();

        $fuzz = new Fuzz();
        $toInsert = [];

        foreach ($questions as $q1) {
            $text1 = $this->normalize($q1->vraag);

            // Compare only against other questions (already in system)
            $others = Question::where('id', '!=', $q1->id)->get();

            foreach ($others as $q2) {
                $text2 = $this->normalize($q2->vraag);

                if (strlen($text1) < 3 || strlen($text2) < 3) {
                    continue;
                }

                $key1 = "{$q1->id}-{$q2->id}";
                $key2 = "{$q2->id}-{$q1->id}";
                if (isset($existing[$key1]) || isset($existing[$key2])) {
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

                if (count($toInsert) >= 500) {
                    QuestionSimilarity::insert($toInsert);
                    $toInsert = [];
                }
            }

            // ✅ Mark this question as fully compared
            $q1->update(['last_compared_at' => now()]);

            $progress->advance();
        }

        if (!empty($toInsert)) {
            QuestionSimilarity::insert($toInsert);
        }

        $progress->finish();
        $this->newLine(2);
        $this->info("✅ Incremental similarity check complete!");
        return Command::SUCCESS;
    }

    protected function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    protected function similarity(string $a, string $b, Fuzz $fuzz): float
    {
        $ratio     = $fuzz->ratio($a, $b) / 100;
        $partial   = $fuzz->partialRatio($a, $b) / 100;
        $tokenSort = $fuzz->tokenSortRatio($a, $b) / 100;
        $tokenSet  = $fuzz->tokenSetRatio($a, $b) / 100;

        $lev = 1 - (levenshtein($a, $b) / max(strlen($a), strlen($b)));
        similar_text($a, $b, $percent);
        $sim = $percent / 100;

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
