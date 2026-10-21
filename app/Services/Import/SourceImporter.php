<?php

namespace App\Services\Import;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Copies the game tables from a source database (default: the read-only `source` connection
 * to the live DB) into the local database. Only reads are ever sent to the source.
 *
 * Privacy: player accounts are imported sanitized (see sanitize()). Local dashboard accounts
 * (created with `dashboard:make-admin`) are stashed before `users` is truncated and restored
 * afterwards (see restoreLocalAccounts()).
 */
class SourceImporter
{
    /** Emails of imported (pseudonymized) players end with this; anything else is a local dashboard account. */
    public const PLAYER_EMAIL_DOMAIN = 'players.invalid';

    /** Local dashboard tables whose rows point at questions that may disappear on re-import. */
    private const QUESTION_REFERENCES = ['dash_question_similarities' => ['question_id', 'similar_question_id'], 'dash_question_reviews' => ['question_id']];

    /** Dashboard columns that hold a users.id, so they can follow a local account that had to move. */
    private const USER_REFERENCES = [
        'dash_review_batches' => 'reviewer_id',
        'dash_question_reviews' => 'reviewer_id',
        'dash_question_similarities' => 'reviewed_by',
        'dash_question_changes' => 'user_id',
    ];

    /** Name of the connection rows are read from. */
    private string $sourceConnection = 'source';

    /** One random bcrypt hash shared by all imported players: nobody can log in as them. */
    private ?string $placeholderHash = null;

    /** Read from another connection (used by dashboard:import-dump). */
    public function from(string $connection): static
    {
        $this->sourceConnection = $connection;

        return $this;
    }

    public function source(): Connection
    {
        return DB::connection($this->sourceConnection);
    }

    /** Guards against a configuration where "local" and "source" are the same database. */
    public function assertSafeTarget(): void
    {
        if ($this->sourceConnection !== 'source') {
            return; // a scratch database; DumpImporter checks the local target itself
        }

        $local = DB::connection()->getConfig();
        $source = config('database.connections.source');

        if (blank($source['database'])) {
            throw new RuntimeException('SOURCE_DB_DATABASE is not set.');
        }

        if ($local['database'] === $source['database'] && in_array($local['host'], [$source['host'], '127.0.0.1', 'localhost'], true)) {
            throw new RuntimeException('The local connection points at the source database. Refusing to import into the live database.');
        }
    }

    /**
     * @param  list<string>  $tables
     * @return array<string, int> row count per table in the source
     */
    public function sourceCounts(array $tables): array
    {
        return collect($tables)->mapWithKeys(fn (string $table) => [$table => $this->source()->table($table)->count()])->all();
    }

    /**
     * Replaces the local rows of the given tables with the source rows.
     *
     * @param  list<string>  $tables  in dependency order
     * @param  callable(string $table, int $copied): void|null  $progress
     * @return array<string, int> rows copied per table
     */
    public function import(array $tables, ?callable $progress = null): array
    {
        $this->assertSafeTarget();

        $chunk = (int) config('dashboard.import.chunk');
        $copied = [];
        $localAccounts = in_array('users', $tables, true) ? $this->stashLocalAccounts() : [];

        try {
            Schema::withoutForeignKeyConstraints(function () use ($tables, $chunk, $progress, &$copied) {
                foreach (array_reverse($tables) as $table) {
                    DB::table($table)->truncate();
                }

                foreach ($tables as $table) {
                    // Tolerant of column differences: only columns that exist on both sides are copied.
                    $columns = array_values(array_intersect(
                        Schema::getColumnListing($table),
                        $this->source()->getSchemaBuilder()->getColumnListing($table),
                    ));
                    $copied[$table] = 0;

                    $this->source()->table($table)->select($columns)->orderBy('id')->chunk($chunk, function ($rows) use ($table, $progress, &$copied) {
                        DB::table($table)->insert($rows->map(fn ($row) => $this->sanitize($table, (array) $row))->all());
                        $copied[$table] += $rows->count();
                        if ($progress) {
                            $progress($table, $copied[$table]);
                        }
                    });
                }
            });
        } finally {
            // Also when the copy fails halfway: never lose a local dashboard login.
            if ($localAccounts !== []) {
                $this->restoreLocalAccounts($localAccounts);
            }
        }

        if (in_array('questions', $tables, true)) {
            $this->afterQuestionsImported();
        }

        return $copied;
    }

    /** Removes personal data from a row before it is stored locally. */
    private function sanitize(string $table, array $row): array
    {
        if ($table === 'users') {
            $row['password'] = $this->placeholderHash ??= Hash::make(Str::random(40));
            $row['remember_token'] = null;
            $row['email'] = 'player'.$row['id'].'@'.self::PLAYER_EMAIL_DOMAIN;
        }

        return $row;
    }

    /** @return list<array<string, mixed>> local dashboard accounts, i.e. users that are not pseudonymized players */
    private function stashLocalAccounts(): array
    {
        return DB::table('users')
            ->where('email', 'not like', '%@'.self::PLAYER_EMAIL_DOMAIN)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    /**
     * Puts the stashed accounts back. An account whose id is taken by an imported player gets a
     * new id above the highest one, and the dashboard rows that point at it are updated.
     *
     * @param  list<array<string, mixed>>  $accounts
     */
    private function restoreLocalAccounts(array $accounts): void
    {
        $maxId = (int) DB::table('users')->max('id');

        foreach ($accounts as $account) {
            $oldId = $account['id'];
            $moved = DB::table('users')->where('id', $oldId)->exists();

            if ($moved) {
                $account['id'] = ++$maxId;
            }

            DB::table('users')->insert($account);

            // Only after the insert: the foreign keys need the new id to exist.
            if ($moved) {
                foreach (self::USER_REFERENCES as $table => $column) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->where($column, $oldId)->update([$column => $account['id']]);
                    }
                }
            }
        }
    }

    private function afterQuestionsImported(): void
    {
        foreach (self::QUESTION_REFERENCES as $table => $columns) {
            foreach ($columns as $column) {
                DB::table($table)->whereNotIn($column, DB::table('questions')->select('id'))->delete();
            }
        }

        // last_compared_at came from the live app's own tooling; make the local
        // similarity check compare every imported question again.
        DB::table('questions')->update(['last_compared_at' => null]);
    }
}
