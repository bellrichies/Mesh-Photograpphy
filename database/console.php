<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Migrations\MigrationRunner;
use Database\Seeders\AboutPageSeeder;
use Database\Seeders\ContactPageSeeder;
use Database\Seeders\CoreCmsSeeder;
use Database\Seeders\DemoContentSeeder;
use Database\Seeders\HeroSlideSeeder;
use Database\Seeders\MediaSampleSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;

$basePath = dirname(__DIR__);
require_once $basePath . '/bootstrap/app.php';

try {
    bootstrapApplication($basePath);

    $defaultConnection = (string) config('database.default', 'mysql');
    $dbConfig = (array) config('database.connections.' . $defaultConnection, []);

    if ($dbConfig === []) {
        throw new RuntimeException('Database connection config not found for: ' . $defaultConnection);
    }

    $database = new Database($dbConfig);
    $runner = new MigrationRunner($database, $basePath . '/database/migrations');

    $command = $argv[1] ?? 'help';

    switch ($command) {
        case 'migrate':
            $runner->migrate();
            exit(0);

        case 'migrate:rollback':
            $runner->rollbackLastBatch();
            exit(0);

        case 'status':
            $runner->status();
            exit(0);

        case 'seed:roles-permissions':
            (new RolePermissionSeeder())->run($database);
            fwrite(STDOUT, "Role and permission seeding completed.\n");
            exit(0);

        case 'seed:core-cms':
            (new CoreCmsSeeder())->run($database);
            fwrite(STDOUT, "Core CMS seeding completed.\n");
            exit(0);

        case 'seed:media-sample':
            (new MediaSampleSeeder())->run($database);
            fwrite(STDOUT, "Sample media seeding completed.\n");
            exit(0);

        case 'seed:settings':
            (new SettingsSeeder())->run($database);
            fwrite(STDOUT, "Settings seeding completed.\n");
            exit(0);

        case 'seed:demo-content':
            (new DemoContentSeeder())->run($database);
            fwrite(STDOUT, "Demo content seeding completed.\n");
            exit(0);

        case 'seed:about-page':
            (new AboutPageSeeder())->run($database);
            fwrite(STDOUT, "About page seeding completed.\n");
            exit(0);

        case 'seed:contact-page':
            (new ContactPageSeeder())->run($database);
            fwrite(STDOUT, "Contact page seeding completed.\n");
            exit(0);

        case 'seed:hero-slides':
            (new HeroSlideSeeder())->run($database);
            fwrite(STDOUT, "Hero slide seeding completed.\n");
            exit(0);

        case 'help':
        default:
            fwrite(STDOUT, "Migration console commands:\n");
            fwrite(STDOUT, "  php database/console.php migrate\n");
            fwrite(STDOUT, "  php database/console.php migrate:rollback\n");
            fwrite(STDOUT, "  php database/console.php status\n");
            fwrite(STDOUT, "  php database/console.php seed:roles-permissions\n");
            fwrite(STDOUT, "  php database/console.php seed:core-cms\n");
            fwrite(STDOUT, "  php database/console.php seed:media-sample\n");
            fwrite(STDOUT, "  php database/console.php seed:settings\n");
            fwrite(STDOUT, "  php database/console.php seed:demo-content\n");
            fwrite(STDOUT, "  php database/console.php seed:about-page\n");
            fwrite(STDOUT, "  php database/console.php seed:contact-page\n");
            fwrite(STDOUT, "  php database/console.php seed:hero-slides\n");
            exit(0);
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'Migration command failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
