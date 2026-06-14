<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['__test_config'] = [];
    }

    /** Seed the in-memory config store used by the config() stub. */
    protected function setConfig(string $key, mixed $value): void
    {
        $GLOBALS['__test_config'][$key] = $value;
    }
}
