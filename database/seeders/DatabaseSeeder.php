<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Database\Seeder;

/**
 * Demo data for trying the dashboard without a live import. Includes a few deliberate
 * near-duplicates so the similarity check has something to find.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $geography = Category::factory()->create(['name' => 'Aardrijkskunde']);
        $art = Category::factory()->create(['name' => 'Kunst']);
        $sport = Category::factory()->create(['name' => 'Sport']);

        $questions = [
            [$geography, 'Wat is de hoofdstad van Frankrijk?', ['Parijs', 'Lyon', 'Marseille', 'Nice']],
            [$geography, 'Welke stad is de hoofdstad van Frankrijk?', ['Parijs', 'Lyon', 'Bordeaux', 'Lille']],
            [$geography, 'Wat is de hoofdstad van Duitsland?', ['Berlijn', 'München', 'Hamburg', 'Keulen']],
            [$geography, 'Wat is de langste rivier van Europa?', ['Wolga', 'Donau', 'Rijn', 'Elbe']],
            [$art, 'Wie schilderde de Nachtwacht?', ['Rembrandt', 'Vermeer', 'Van Gogh', 'Frans Hals']],
            [$art, 'Welke schilder maakte De Nachtwacht?', ['Rembrandt', 'Vermeer', 'Jan Steen', 'Mondriaan']],
            [$art, 'Wie schilderde Het meisje met de parel?', ['Vermeer', 'Rembrandt', 'Van Gogh', 'Breitner']],
            [$sport, 'Welke voetbalclub won in 1995 de Champions League?', ['Ajax', 'AC Milan', 'Juventus', 'Bayern München']],
            [$sport, 'Welke voetbal club won in 1988 de Europacup I?', ['PSV', 'Benfica', 'Ajax', 'Feyenoord']],
            [$sport, 'Hoeveel spelers staan er bij voetbal per team op het veld?', ['11', '10', '12', '9']],
        ];

        foreach ($questions as [$category, $text, $answers]) {
            Question::factory()
                ->withAnswers(...$answers)
                ->create(['vraag' => $text, 'category_id' => $category->id, 'difficulty' => fake()->numberBetween(1, 5)]);
        }

        Question::factory(40)->withAnswers('A', 'B', 'C', 'D')->create(['category_id' => $sport->id]);
    }
}
