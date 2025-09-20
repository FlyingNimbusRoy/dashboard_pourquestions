<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;
use App\Models\QuestionSimilarity;

class CheckQuestionSimilarities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Run with: php artisan questions:check-similarities
     */
    protected $signature = 'questions:check-similarities';

    /**
     * The console command description.
     */
    protected $description = 'Check for similar questions and store them in question_similarities table';

    public function handle()
    {
        $this->info("Fetching all questions...");
        $questions = Question::all();

        $count = $questions->count();
        $this->info("Comparing {$count} questions...");

        foreach ($questions as $i => $q1) {
            foreach ($questions as $j => $q2) {
                if ($q1->id >= $q2->id) continue;

                $exists = QuestionSimilarity::where('question_id', $q1->id)
                    ->where('similar_question_id', $q2->id)
                    ->exists();

                if ($exists) continue;

                $score = $this->similarity($q1->vraag, $q2->vraag);

                if ($score >= 0.7) {
                    QuestionSimilarity::create([
                        'question_id'          => $q1->id,
                        'similar_question_id'  => $q2->id,
                        'similarity_score'     => $score,
                        'handled'              => false,
                    ]);
                    $this->line("🔗 Found similarity: {$q1->id} ↔ {$q2->id} (score {$score})");
                }
            }
        }

        $this->info("✅ Done checking similarities!");
        return Command::SUCCESS;
    }

    /**
     * Compute similarity score between two strings.
     */
    protected function similarity(string $a, string $b): float
    {
        similar_text(mb_strtolower($a), mb_strtolower($b), $percent);
        return $percent / 100;
    }
}
