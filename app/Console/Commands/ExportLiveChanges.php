<?php

namespace App\Console\Commands;

use App\Services\Insights\LiveChangeExporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('dashboard:export-live-changes {--path= : Output file (default storage/app/live-changes-DATE.sql)} {--dry-run : Print the SQL, do not mark as exported}')]
#[Description('Write pending live changes to a reviewable SQL file and mark them exported (never executes it).')]
class ExportLiveChanges extends Command
{
    public function handle(LiveChangeExporter $exporter): int
    {
        $result = $exporter->build();
        if ($result['count'] === 0) {
            $this->info('No pending live changes.');

            return self::SUCCESS;
        }
        if ($this->option('dry-run')) {
            $this->line($result['sql']);

            return self::SUCCESS;
        }
        $path = $this->option('path') ?: storage_path('app/live-changes-'.now()->format('Ymd-His').'.sql');
        file_put_contents($path, $result['sql']);
        $exporter->markExported($result['ids'], null, basename($path), $result['sql']);
        $this->info("Wrote {$result['count']} changes to $path");

        return self::SUCCESS;
    }
}
