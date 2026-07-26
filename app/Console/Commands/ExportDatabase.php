<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Database\DatabaseExportService;
use Illuminate\Console\Command;
use Throwable;

class ExportDatabase extends Command
{
    protected $signature = 'db:export
                            {--connection= : Database connection name}
                            {--path= : Absolute output path}';

    protected $description = 'Export a database to a SQL dump file';

    public function handle(DatabaseExportService $exporter): int
    {
        try {
            $path = $exporter->export(
                $this->stringOption('connection'),
                $this->stringOption('path'),
            );
        } catch (Throwable $exception) {
            report($exception);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Database exported successfully: {$path}");

        return self::SUCCESS;
    }

    private function stringOption(string $name): ?string
    {
        $value = $this->option($name);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
