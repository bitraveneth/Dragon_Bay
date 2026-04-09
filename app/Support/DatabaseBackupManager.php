<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseBackupManager
{
    protected const DISK = 'local';

    protected const DIRECTORY = 'backups/database';

    public function list(): array
    {
        $disk = Storage::disk(self::DISK);

        if (! $disk->exists(self::DIRECTORY)) {
            return [];
        }

        return collect($disk->files(self::DIRECTORY))
            ->filter(fn (string $path) => str_ends_with($path, '.sql'))
            ->sortByDesc(fn (string $path) => $disk->lastModified($path))
            ->values()
            ->map(function (string $path) use ($disk) {
                return [
                    'filename' => basename($path),
                    'path' => $path,
                    'size' => $disk->size($path),
                    'size_label' => $this->humanBytes($disk->size($path)),
                    'last_modified_at' => Carbon::createFromTimestamp($disk->lastModified($path)),
                ];
            })
            ->all();
    }

    public function create(): string
    {
        $config = $this->mysqlConfig();
        $disk = Storage::disk(self::DISK);

        if (! $disk->exists(self::DIRECTORY)) {
            $disk->makeDirectory(self::DIRECTORY);
        }

        $filename = sprintf(
            '%s-backup-%s.sql',
            str($config['database'])->slug()->value(),
            now()->format('Ymd-His')
        );

        $relativePath = self::DIRECTORY . '/' . $filename;
        $absolutePath = $disk->path($relativePath);
        $handle = fopen($absolutePath, 'wb');

        if ($handle === false) {
            throw new RuntimeException('Unable to create the backup file.');
        }

        $process = new Process($this->dumpCommand($config), base_path(), [
            'MYSQL_PWD' => $config['password'] ?? '',
        ]);
        $process->setTimeout(600);
        $errorOutput = '';

        try {
            $process->mustRun(function (string $type, string $buffer) use ($handle, &$errorOutput) {
                if ($type === Process::ERR) {
                    $errorOutput .= $buffer;

                    return;
                }

                fwrite($handle, $buffer);
            });
        } catch (ProcessFailedException $exception) {
            fclose($handle);
            File::delete($absolutePath);

            throw new RuntimeException('Database backup failed: ' . trim($errorOutput ?: $exception->getProcess()->getErrorOutput()));
        }

        fclose($handle);

        return $filename;
    }

    public function restore(string $filename): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException(
                'In-app database restore is disabled outside local/testing because a partial restore can corrupt the live database. Use an offline restore procedure instead.'
            );
        }

        $config = $this->mysqlConfig();
        $relativePath = self::DIRECTORY . '/' . basename($filename);
        $disk = Storage::disk(self::DISK);

        if (! $disk->exists($relativePath)) {
            throw new RuntimeException('Selected backup file was not found.');
        }

        $stream = fopen($disk->path($relativePath), 'r');

        if ($stream === false) {
            throw new RuntimeException('Unable to open the backup file for restore.');
        }

        $process = new Process($this->restoreCommand($config), base_path(), [
            'MYSQL_PWD' => $config['password'] ?? '',
        ]);
        $process->setTimeout(600);
        $process->setInput($stream);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            throw new RuntimeException('Database restore failed: ' . trim($exception->getProcess()->getErrorOutput()));
        } finally {
            fclose($stream);
        }
    }

    public function relativePath(string $filename): string
    {
        return self::DIRECTORY . '/' . basename($filename);
    }

    protected function mysqlConfig(): array
    {
        $defaultConnection = Config::get('database.default');
        $config = Config::get("database.connections.{$defaultConnection}");

        if (($config['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('Backup and restore currently support MySQL connections only.');
        }

        if (empty($config['database']) || empty($config['username'])) {
            throw new RuntimeException('Database connection settings are incomplete.');
        }

        return $config;
    }

    protected function dumpCommand(array $config): array
    {
        return array_values(array_filter([
            'mysqldump',
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--default-character-set=utf8mb4',
            $this->hostFlag($config),
            $this->portFlag($config),
            $this->socketFlag($config),
            $this->userFlag($config),
            $config['database'],
        ]));
    }

    protected function restoreCommand(array $config): array
    {
        return array_values(array_filter([
            'mysql',
            $this->hostFlag($config),
            $this->portFlag($config),
            $this->socketFlag($config),
            $this->userFlag($config),
            $config['database'],
        ]));
    }

    protected function hostFlag(array $config): ?string
    {
        if (! empty($config['unix_socket'])) {
            return null;
        }

        return ! empty($config['host']) ? '--host=' . $config['host'] : null;
    }

    protected function portFlag(array $config): ?string
    {
        if (! empty($config['unix_socket'])) {
            return null;
        }

        return ! empty($config['port']) ? '--port=' . $config['port'] : null;
    }

    protected function socketFlag(array $config): ?string
    {
        return ! empty($config['unix_socket']) ? '--socket=' . $config['unix_socket'] : null;
    }

    protected function userFlag(array $config): ?string
    {
        return ! empty($config['username']) ? '--user=' . $config['username'] : null;
    }

    protected function humanBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = $bytes / 1024;
        $unitIndex = 0;

        while ($value >= 1024 && $unitIndex < count($units) - 1) {
            $value /= 1024;
            $unitIndex++;
        }

        return number_format($value, $value >= 10 ? 0 : 1) . ' ' . $units[$unitIndex];
    }
}
