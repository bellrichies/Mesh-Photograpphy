<?php

declare(strict_types=1);

$basePath = dirname(__DIR__);

require $basePath . '/bootstrap/app.php';

try {
	$app = bootstrapApplication($basePath);
	$app->run();
} catch (Throwable $exception) {
	if (! class_exists(\App\Core\ErrorHandler::class, false)) {
		require_once $basePath . '/vendor/autoload.php';
		require_once $basePath . '/bootstrap/helpers.php';
	}

	$request = new \App\Core\Request();
	$handler = new \App\Core\ErrorHandler($basePath, $request);
	$handler->handle($exception)->send();
}
