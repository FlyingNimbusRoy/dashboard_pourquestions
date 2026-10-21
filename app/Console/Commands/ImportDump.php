<?php

namespace App\Console\Commands;

use App\Services\Import\DumpImporter;
use App\Services\Import\SourceImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('dashboard:import-dump {path : phpMyAdmin/mysqldump export (structure + data) of the live database} {--force : Skip the confirmation}')]
#[Description('Replace the local game data with the contents of a full SQL dump (no SSH tunnel needed)')]
class ImportDump extends Command
{
    public function handle(DumpImporter $dumps): int
    {
        try {
            $dumps->assertLocalTarget();
        } catch (Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('This REPLACES all local game tables with the dump (local edits are lost; the audit log is kept). Continue?')) {
            return self::SUCCESS;
        }

        try {
            $copied = $dumps->withDump((string) $this->argument('path'), function (SourceImporter $importer) {
                $this->components->info('Dump loaded into a scratch database, copying...');

                return $importer->import(config('dashboard.import.tables'));
            });
        } catch (Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(['Table', 'Rows imported'], collect($copied)->map(fn ($count, $table) => [$table, $count])->values());
        $this->components->info('Imported '.array_sum($copied).' rows. Now run `php artisan questions:detect-similar --full` to refresh the similarity pairs.');

        return self::SUCCESS;
    }
}
