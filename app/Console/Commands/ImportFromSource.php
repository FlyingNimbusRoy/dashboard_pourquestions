<?php

namespace App\Console\Commands;

use App\Services\Import\SourceImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('dashboard:import {--table=* : Only these tables (default: config dashboard.import.tables)} {--force : Skip the confirmation}')]
#[Description('Replace the local game data with a read-only copy of the live database (needs the SSH tunnel)')]
class ImportFromSource extends Command
{
    public function handle(SourceImporter $importer): int
    {
        $tables = $this->option('table') ?: config('dashboard.import.tables');

        try {
            $importer->assertSafeTarget();
            $counts = $importer->sourceCounts($tables);
        } catch (Throwable $e) {
            $this->components->error('Cannot read the source database: '.$e->getMessage());
            $this->line('  Is the SSH tunnel open and are the SOURCE_DB_* variables set? See README.md.');

            return self::FAILURE;
        }

        $this->table(['Table', 'Rows in source'], collect($counts)->map(fn ($count, $table) => [$table, $count])->values());

        if (! $this->option('force') && ! $this->confirm('This REPLACES these local tables (local edits are lost; the audit log is kept). Continue?')) {
            return self::SUCCESS;
        }

        $copied = $importer->import($tables, function (string $table, int $rows) {
            $this->output->write("\r  {$table}: {$rows} rows");
        });

        $this->newLine();
        $this->table(['Table', 'Rows imported'], collect($copied)->map(fn ($count, $table) => [$table, $count])->values());
        $this->components->info('Imported '.array_sum($copied).' rows. Run `php artisan questions:detect-similar --full` to refresh the similarity pairs.');

        return self::SUCCESS;
    }
}
