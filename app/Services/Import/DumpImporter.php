<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Loads a phpMyAdmin/mysqldump export (structure + data) into a scratch database next to the
 * local one, so SourceImporter can copy it over with exactly the same code path (and the same
 * sanitizing) as a live import. The scratch database is dropped afterwards.
 */
class DumpImporter
{
    private const SCRATCH = 'import_dump';

    private const LOCAL_HOSTS = ['db', '127.0.0.1', 'localhost'];

    /** Refuses to run unless the default connection is the local development database. */
    public function assertLocalTarget(): void
    {
        $local = DB::connection()->getConfig();
        $sourceDatabase = config('database.connections.source.database');

        if (! app()->environment('local')
            || ! in_array($local['host'], self::LOCAL_HOSTS, true)
            || (filled($sourceDatabase) && $local['database'] === $sourceDatabase)) {
            throw new RuntimeException('dashboard:import-dump only runs against the local development database (APP_ENV=local, host db/localhost).');
        }
    }

    /**
     * Loads the dump into the scratch database and runs $callback with an importer reading from it.
     * The scratch database is always dropped again.
     *
     * @template T
     *
     * @param  callable(SourceImporter): T  $callback
     * @return T
     */
    public function withDump(string $path, callable $callback): mixed
    {
        $this->assertLocalTarget();

        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException("Cannot read dump file: {$path}");
        }

        $this->assertNoDatabaseSwitch($path);

        $scratch = config('dashboard.import.scratch_database');
        $local = DB::connection()->getConfig();
        $root = array_merge($local, [
            'database' => null,
            'username' => config('dashboard.import.root_username'),
            'password' => config('dashboard.import.root_password'),
        ]);

        config(['database.connections.import_root' => $root]);
        $admin = fn () => DB::connection('import_root');

        try {
            $admin()->statement("DROP DATABASE IF EXISTS `{$scratch}`");
            $admin()->statement("CREATE DATABASE `{$scratch}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            $this->load($path, $root, $scratch);

            config(['database.connections.'.self::SCRATCH => array_merge($root, ['database' => $scratch])]);

            return $callback((new SourceImporter)->from(self::SCRATCH));
        } finally {
            $admin()->statement("DROP DATABASE IF EXISTS `{$scratch}`");
            DB::purge(self::SCRATCH);
            DB::purge('import_root');
        }
    }

    /** A dump that creates or selects its own database would escape the scratch database. */
    private function assertNoDatabaseSwitch(string $path): void
    {
        $handle = fopen($path, 'rb');

        while (($line = fgets($handle)) !== false) {
            if (preg_match('/^\s*(CREATE\s+DATABASE|USE\s+`?\w+`?\s*;|DROP\s+DATABASE)/i', $line)) {
                fclose($handle);
                throw new RuntimeException('The dump contains a CREATE/DROP DATABASE or USE statement. Export again without "Add CREATE DATABASE".');
            }
        }

        fclose($handle);
    }

    private function load(string $path, array $root, string $scratch): void
    {
        $handle = fopen($path, 'rb');

        $process = new Process(
            ['mariadb', '--host='.$root['host'], '--port='.$root['port'], '--user='.$root['username'], '--skip-ssl', '--default-character-set=utf8mb4', $scratch],
            null,
            ['MYSQL_PWD' => (string) $root['password']],
            $handle,
            null,
        );
        $process->run();
        fclose($handle);

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Loading the dump failed: '.trim($process->getErrorOutput()));
        }
    }
}
