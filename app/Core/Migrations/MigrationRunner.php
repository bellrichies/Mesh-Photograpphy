<?php

declare(strict_types=1);

namespace App\Core\Migrations;

use App\Core\Database;
use RuntimeException;
use Throwable;

class MigrationRunner
{
    public function __construct(
        private readonly Database $database,
        private readonly string $migrationPath
    ) {
    }

    public function migrate(): int
    {
        $this->ensureMigrationsTable();

        $executed = $this->executedMigrations();
        $files = $this->migrationFiles();
        $pending = array_values(array_filter($files, static fn (string $file): bool => ! isset($executed[basename($file)])));

        if ($pending === []) {
            $this->line('No pending migrations.');
            return 0;
        }

        $batch = $this->currentBatch() + 1;
        $ranCount = 0;

        foreach ($pending as $file) {
            $migrationName = basename($file);
            $this->line('Migrating: ' . $migrationName);

            $this->runInTransaction(function () use ($file, $migrationName, $batch): void {
                $migration = $this->resolveMigration($file);
                $migration->up();

                $this->database->query(
                    'INSERT INTO migrations (migration, batch, ran_at) VALUES (:migration, :batch, NOW())',
                    ['migration' => $migrationName, 'batch' => $batch]
                );
            });

            $ranCount++;
            $this->line('Migrated: ' . $migrationName);
        }

        $this->line('Migration complete. Ran ' . $ranCount . ' migration(s).');

        return $ranCount;
    }

    public function rollbackLastBatch(): int
    {
        $this->ensureMigrationsTable();

        $batch = $this->currentBatch();
        if ($batch <= 0) {
            $this->line('No migration batch found to rollback.');
            return 0;
        }

        $rows = $this->database->query(
            'SELECT id, migration FROM migrations WHERE batch = :batch ORDER BY id DESC',
            ['batch' => $batch]
        )->fetchAll();

        if (! is_array($rows) || $rows === []) {
            $this->line('No migrations found in the latest batch.');
            return 0;
        }

        $filesByName = [];
        foreach ($this->migrationFiles() as $file) {
            $filesByName[basename($file)] = $file;
        }

        $rolledBack = 0;

        foreach ($rows as $row) {
            $migrationName = (string) ($row['migration'] ?? '');
            $id = (int) ($row['id'] ?? 0);

            if (! isset($filesByName[$migrationName])) {
                $this->line('Skipping missing migration file: ' . $migrationName);
                continue;
            }

            $this->line('Rolling back: ' . $migrationName);

            $this->runInTransaction(function () use ($filesByName, $migrationName, $id): void {
                $migration = $this->resolveMigration($filesByName[$migrationName]);
                $migration->down();

                $this->database->query('DELETE FROM migrations WHERE id = :id', ['id' => $id]);
            });

            $rolledBack++;
            $this->line('Rolled back: ' . $migrationName);
        }

        $this->line('Rollback complete. Reverted ' . $rolledBack . ' migration(s).');

        return $rolledBack;
    }

    public function status(): int
    {
        $this->ensureMigrationsTable();

        $executed = $this->executedMigrations();
        $files = $this->migrationFiles();

        if ($files === []) {
            $this->line('No migration files found.');
            return 0;
        }

        $this->line('Migration status:');
        foreach ($files as $file) {
            $name = basename($file);
            $status = isset($executed[$name]) ? 'Y' : 'N';
            $batch = isset($executed[$name]) ? (string) $executed[$name] : '-';
            $this->line(sprintf('  [%s] batch=%s %s', $status, $batch, $name));
        }

        return 0;
    }

    private function ensureMigrationsTable(): void
    {
        $this->database->query(
            'CREATE TABLE IF NOT EXISTS migrations (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT UNSIGNED NOT NULL,
                ran_at DATETIME NOT NULL,
                INDEX idx_migrations_batch (batch)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * @return array<string, int>
     */
    private function executedMigrations(): array
    {
        $rows = $this->database->query('SELECT migration, batch FROM migrations ORDER BY id ASC')->fetchAll();
        if (! is_array($rows)) {
            return [];
        }

        $items = [];
        foreach ($rows as $row) {
            $name = (string) ($row['migration'] ?? '');
            $batch = (int) ($row['batch'] ?? 0);
            if ($name !== '') {
                $items[$name] = $batch;
            }
        }

        return $items;
    }

    private function currentBatch(): int
    {
        $result = $this->database->query('SELECT MAX(batch) AS batch FROM migrations')->fetchColumn();
        return (int) ($result ?: 0);
    }

    /**
     * @return array<int, string>
     */
    private function migrationFiles(): array
    {
        $pattern = rtrim($this->migrationPath, '/\\') . DIRECTORY_SEPARATOR . '*.php';
        $files = glob($pattern) ?: [];
        sort($files);
        return $files;
    }

    private function resolveMigration(string $file): Migration
    {
        $db = $this->database->connection();
        $migration = require $file;

        if ($migration instanceof Migration) {
            return $migration;
        }

        if (is_string($migration) && class_exists($migration)) {
            $instance = new $migration($db);
            if ($instance instanceof Migration) {
                return $instance;
            }
        }

        throw new RuntimeException('Migration file must return a Migration instance: ' . basename($file));
    }

    private function runInTransaction(callable $callback): void
    {
        $pdo = $this->database->connection();

        $pdo->beginTransaction();
        try {
            $callback();
            if ($pdo->inTransaction()) {
                $pdo->commit();
            }
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function line(string $message): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }
}
