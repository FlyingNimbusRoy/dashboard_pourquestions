<?php

namespace Tests\Feature;

use App\Enums\SimilarityStatus;
use App\Models\Question;
use App\Models\QuestionSimilarity;
use App\Services\Import\SourceImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

/** The "live" source is simulated with an in-memory SQLite database. */
class SourceImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_question_data_and_cleans_up_local_references(): void
    {
        $this->fakeSource();
        $stale = Question::factory()->create();
        $staleTwin = Question::factory()->create();
        QuestionSimilarity::create(['question_id' => $stale->id, 'similar_question_id' => $staleTwin->id, 'score' => 0.9, 'signals' => [], 'status' => SimilarityStatus::Open]);

        $copied = app(SourceImporter::class)->import(['categories', 'questions', 'answers']);

        $this->assertSame(['categories' => 1, 'questions' => 2, 'answers' => 3], $copied);
        $this->assertSame(['Wat is de hoofdstad van Frankrijk?', 'Wie schilderde de Nachtwacht?'], Question::orderBy('id')->pluck('vraag')->all());
        $this->assertSame(0, QuestionSimilarity::count(), 'pairs pointing at replaced questions are removed');
        $this->assertSame(0, Question::whereNotNull('last_compared_at')->count(), 'imported questions are re-compared locally');
    }

    public function test_refuses_when_local_and_source_are_the_same_database(): void
    {
        config(['database.connections.source' => config('database.connections.mariadb')]);

        $this->expectException(RuntimeException::class);
        app(SourceImporter::class)->assertSafeTarget();
    }

    public function test_the_source_connection_is_read_only(): void
    {
        $this->assertSame(
            'SET SESSION TRANSACTION READ ONLY',
            config('database.connections.source.options')[\Pdo\Mysql::ATTR_INIT_COMMAND] ?? null,
        );
    }

    private function fakeSource(): void
    {
        config(['database.connections.source' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::purge('source');
        $schema = Schema::connection('source');

        $schema->create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        $schema->create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('maker_id');
            $table->string('vraag');
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('difficulty')->default(3);
            $table->timestamp('last_compared_at')->nullable();
            $table->timestamp('last_validated');
            $table->string('column_only_on_live')->nullable();
            $table->timestamps();
        });
        $schema->create('answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('question_id');
            $table->string('answer');
            $table->boolean('is_correct');
            $table->timestamps();
        });

        $source = DB::connection('source');
        $source->table('categories')->insert(['id' => 7, 'name' => 'Algemeen']);
        $source->table('questions')->insert([
            ['id' => 500, 'maker_id' => 3, 'vraag' => 'Wat is de hoofdstad van Frankrijk?', 'category_id' => 7, 'last_compared_at' => '2026-01-01 00:00:00', 'last_validated' => '2020-12-31 23:00:00', 'column_only_on_live' => 'x'],
            ['id' => 501, 'maker_id' => 3, 'vraag' => 'Wie schilderde de Nachtwacht?', 'category_id' => 7, 'last_compared_at' => null, 'last_validated' => '2025-05-01 12:00:00', 'column_only_on_live' => null],
        ]);
        $source->table('answers')->insert([
            ['question_id' => 500, 'answer' => 'Parijs', 'is_correct' => true],
            ['question_id' => 500, 'answer' => 'Lyon', 'is_correct' => false],
            ['question_id' => 501, 'answer' => 'Rembrandt', 'is_correct' => true],
        ]);
    }
}
