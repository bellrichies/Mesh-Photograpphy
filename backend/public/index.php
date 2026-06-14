<?php

declare(strict_types=1);

$basePath = dirname(__DIR__);

require $basePath . '/bootstrap/app.php';

$app = bootstrapApplication($basePath);
$app->run();
