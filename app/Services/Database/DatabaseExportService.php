<?php

declare(strict_types=1);

namespace App\Services\Database;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class DatabaseExportService
{
    public function export(?string $connectionName = null, ?string $path = null): string
    {
        $connectionName ??= (string) config('database.default');
        $connection = config("database.connections.{$connectionName}");

        if (! is_array($connection)) {
            throw new InvalidArgumentException("Database connection [{$connectionName}] is not configured.");
        }

        $driver = (string) ($connection['driver'] ?? '');
        $path ??= $this->defaultPath($connectionName);

        File::ensureDirectoryExists(dirname($path), 0700);

        $stream = fopen($path, 'wb');

        if ($stream === false) {
            throw new RuntimeException("Unable to create database export at [{$path}].");
        }

        try {
            $process = $this->process($driver, $connection);
            $process->setTimeout(null);
            $process->run(function (string $type, string $buffer) use ($stream): void {
                if ($type === Process::OUT) {
                    fwrite($stream, $buffer);
                }
            });

            if (! $process->isSuccessful()) {
                $error = trim($process->getErrorOutput());

                throw new RuntimeException(
                    'Database export failed'.($error !== '' ? ": {$error}" : '.')
                );
            }
        } catch (Throwable $exception) {
            fclose($stream);
            File::delete($path);

            throw $exception;
        }

        fclose($stream);
        chmod($path, 0600);

        return $path;
    }

    public function defaultPath(string $connectionName): string
    {
        $safeConnection = preg_replace('/[^A-Za-z0-9_-]+/', '-', $connectionName) ?: 'database';

        return storage_path(sprintf(
            'app/private/database-exports/%s-%s.sql',
            $safeConnection,
            now()->format('Y-m-d_H-i-s-u'),
        ));
    }

    /**
     * @param  array<string, mixed>  $connection
     */
    private function process(string $driver, array $connection): Process
    {
        return match ($driver) {
            'mysql', 'mariadb' => $this->mysqlProcess($connection),
            'pgsql' => $this->postgresProcess($connection),
            'sqlite' => $this->sqliteProcess($connection),
            default => throw new InvalidArgumentException(
                "Database driver [{$driver}] is not supported for export."
            ),
        };
    }

    /**
     * @param  array<string, mixed>  $connection
     */
    private function mysqlProcess(array $connection): Process
    {
        $command = [
            (string) config('database-export.binaries.mysql', 'mysqldump'),
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--no-tablespaces',
            '--host='.$this->required($connection, 'host'),
            '--port='.(string) ($connection['port'] ?? 3306),
            '--user='.$this->required($connection, 'username'),
            '--default-character-set='.(string) ($connection['charset'] ?? 'utf8mb4'),
            $this->required($connection, 'database'),
        ];

        return new Process($command, env: [
            'MYSQL_PWD' => (string) ($connection['password'] ?? ''),
        ]);
    }

    /**
     * @param  array<string, mixed>  $connection
     */
    private function postgresProcess(array $connection): Process
    {
        return new Process([
            (string) config('database-export.binaries.pgsql', 'pg_dump'),
            '--host='.$this->required($connection, 'host'),
            '--port='.(string) ($connection['port'] ?? 5432),
            '--username='.$this->required($connection, 'username'),
            '--dbname='.$this->required($connection, 'database'),
            '--no-owner',
            '--no-privileges',
            '--format=plain',
        ], env: [
            'PGPASSWORD' => (string) ($connection['password'] ?? ''),
        ]);
    }

    /**
     * @param  array<string, mixed>  $connection
     */
    private function sqliteProcess(array $connection): Process
    {
        return new Process([
            (string) config('database-export.binaries.sqlite', 'sqlite3'),
            $this->required($connection, 'database'),
            '.dump',
        ]);
    }

    /**
     * @param  array<string, mixed>  $connection
     */
    private function required(array $connection, string $key): string
    {
        $value = $connection[$key] ?? null;

        if (! is_string($value) || $value === '') {
            throw new InvalidArgumentException("Database connection value [{$key}] is required.");
        }

        return $value;
    }
}
