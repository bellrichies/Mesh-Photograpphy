#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Database CLI
 *
 * Usage (run from backend/ directory):
 *   php database/console.php migrate
 *   php database/console.php migrate:rollback
 *   php database/console.php seed:roles-permissions
 *   php database/console.php seed:admin
 *   php database/console.php seed:core-cms
 *   php database/console.php seed:all
 */

$basePath = dirname(__DIR__);

// Load Composer autoload + dotenv
require $basePath . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable($basePath);
$dotenv->safeLoad();

function consoleEnv(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
    if ($value === 'true')  return true;
    if ($value === 'false') return false;
    if ($value === 'null')  return null;
    return $value;
}

function getConsolePdo(): PDO
{
    $host    = consoleEnv('DB_HOST', '127.0.0.1');
    $port    = consoleEnv('DB_PORT', '3306');
    $db      = consoleEnv('DB_DATABASE', 'mesh_photo');
    $user    = consoleEnv('DB_USERNAME', 'root');
    $pass    = consoleEnv('DB_PASSWORD', '');
    $charset = consoleEnv('DB_CHARSET', 'utf8mb4');

    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
    return new PDO($dsn, $user, (string) $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}

function ensureMigrationsTable(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
            migration VARCHAR(255) NOT NULL,
            ran_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_migrations_migration (migration)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function runMigrate(PDO $pdo, string $basePath): void
{
    ensureMigrationsTable($pdo);

    $migrationsDir = $basePath . '/database/migrations';
    $files         = glob($migrationsDir . '/*.php');
    sort($files);

    $ran   = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
    $count = 0;

    foreach ($files as $file) {
        $name = basename($file);
        if (in_array($name, $ran, true)) {
            echo "  [skip] {$name}\n";
            continue;
        }

        // Migration files return an anonymous class instance
        $migration = require $file;
        $desc = $migration->description ?? $name;
        echo "  [run]  {$desc} ({$name}) ... ";
        $migration->up($pdo);
        $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)")->execute([$name]);
        echo "done\n";
        $count++;
    }

    echo $count === 0 ? "  Nothing new to migrate.\n" : "  Done. {$count} migration(s) ran.\n";
}

function runRollback(PDO $pdo, string $basePath): void
{
    ensureMigrationsTable($pdo);

    $last = $pdo->query("SELECT migration FROM migrations ORDER BY id DESC LIMIT 1")->fetchColumn();
    if (!$last) {
        echo "  Nothing to roll back.\n";
        return;
    }

    $file = $basePath . '/database/migrations/' . $last;
    if (!file_exists($file)) {
        echo "  Migration file not found: {$last}\n";
        return;
    }

    $migration = require $file;
    $desc = $migration->description ?? $last;
    echo "  [rollback] {$desc} ({$last}) ... ";
    $migration->down($pdo);
    $pdo->prepare("DELETE FROM migrations WHERE migration = ?")->execute([$last]);
    echo "done\n";
}

function runSeeder(PDO $pdo, string $basePath, string $class, string $fileName): void
{
    $file = $basePath . '/database/seeders/' . $fileName;
    if (!file_exists($file)) {
        echo "  Seeder not found: {$file}\n";
        return;
    }
    require_once $file;
    $seeder = new $class();
    $seeder->run($pdo);
}

// ─── Main ────────────────────────────────────────────────────────────────────

$command = $argv[1] ?? '';

if (!$command) {
    echo "Usage:\n";
    echo "  php database/console.php migrate\n";
    echo "  php database/console.php migrate:rollback\n";
    echo "  php database/console.php seed:roles-permissions\n";
    echo "  php database/console.php seed:admin\n";
    echo "  php database/console.php seed:core-cms\n";
    echo "  php database/console.php seed:all\n";
    echo "  php database/console.php seed:production\n";
    exit(0);
}

try {
    $pdo = getConsolePdo();
    echo "Command: {$command}\n";

    match ($command) {
        'migrate'                  => runMigrate($pdo, $basePath),
        'migrate:rollback'         => runRollback($pdo, $basePath),
        'seed:roles-permissions'   => runSeeder($pdo, $basePath, 'RolesPermissionsSeeder', 'RolesPermissionsSeeder.php'),
        'seed:admin'               => runSeeder($pdo, $basePath, 'AdminUserSeeder',         'AdminUserSeeder.php'),
        'seed:core-cms'            => runSeeder($pdo, $basePath, 'CoreCmsSeeder',            'CoreCmsSeeder.php'),
        'seed:all'                 => (function () use ($pdo, $basePath): void {
            runSeeder($pdo, $basePath, 'RolesPermissionsSeeder', 'RolesPermissionsSeeder.php');
            runSeeder($pdo, $basePath, 'AdminUserSeeder',         'AdminUserSeeder.php');
            runSeeder($pdo, $basePath, 'CoreCmsSeeder',           'CoreCmsSeeder.php');
        })(),
        'seed:production'          => runSeeder($pdo, $basePath, 'ProductionSeeder', 'ProductionSeeder.php'),
        default => (function () use ($command): void {
            echo "Unknown command: {$command}\n";
            exit(1);
        })(),
    };
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
